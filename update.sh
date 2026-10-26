#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

maintenance_enabled=0

log() {
    printf '\n==> %s\n' "$1"
}

fail() {
    printf 'Error: %s\n' "$1" >&2
    exit 1
}

ask_yes_no() {
    local prompt="$1"
    local answer

    while true; do
        printf '%s [y/n]: ' "$prompt"
        IFS= read -r answer || fail "Could not read your answer. Run this script from an interactive terminal."

        case "$answer" in
            [Yy]|[Yy][Ee][Ss]) return 0 ;;
            [Nn]|[Nn][Oo]) return 1 ;;
            *) printf 'Please answer y or n.\n' ;;
        esac
    done
}

run_artisan() {
    php artisan "$@"
}

cleanup() {
    local exit_code=$?
    trap - EXIT

    if (( maintenance_enabled )); then
        printf '\nRestoring the application from maintenance mode...\n' >&2
        if run_artisan up; then
            printf 'Application is live again.\n' >&2
        else
            printf 'Error: Could not disable maintenance mode. Run `php artisan up` manually.\n' >&2
            if (( exit_code == 0 )); then
                exit_code=1
            fi
        fi
    fi

    exit "$exit_code"
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

[[ -f artisan ]] || fail "Run update.sh from a Laravel project containing artisan."
command -v git >/dev/null 2>&1 || fail "git is required but was not found."
command -v php >/dev/null 2>&1 || fail "php is required but was not found."
git rev-parse --is-inside-work-tree >/dev/null 2>&1 || fail "This project directory is not a Git working tree."

if [[ -n "$(git status --porcelain)" ]]; then
    fail "The working tree is not clean. Commit, stash, or remove local changes before updating."
fi

composer_available=0
if command -v composer >/dev/null 2>&1 || [[ -f composer.phar ]]; then
    composer_available=1
fi

install_composer=0
if ask_yes_no "Install/update production Composer dependencies?"; then
    install_composer=1
    (( composer_available )) || fail "Composer was selected but neither composer nor composer.phar is available."
fi

run_migrations=0
if ask_yes_no "Run pending database migrations?"; then
    run_migrations=1
fi

apply_permissions=0
web_owner=""
web_group=""
if ask_yes_no "Set project ownership and Laravel writable-directory permissions?"; then
    command -v chown >/dev/null 2>&1 || fail "chown is required to change project ownership."
    command -v chmod >/dev/null 2>&1 || fail "chmod is required to set directory permissions."
    command -v id >/dev/null 2>&1 || fail "id is required to validate the web-server account."
    command -v getent >/dev/null 2>&1 || fail "getent is required to validate the web-server group."

    printf 'Web-server owner (user or user:group): '
    IFS= read -r ownership || fail "Could not read the web-server account."
    [[ -n "$ownership" ]] || fail "A web-server user is required when applying permissions."

    web_owner="${ownership%%:*}"
    if [[ "$ownership" == *:* ]]; then
        web_group="${ownership#*:}"
        [[ -n "$web_group" && "$web_group" != *:* ]] || fail "Enter a valid user or user:group."
        getent group "$web_group" >/dev/null || fail "Group '$web_group' does not exist."
    else
        web_group="$(id -gn "$web_owner")" || fail "User '$web_owner' does not exist."
    fi

    id -u "$web_owner" >/dev/null 2>&1 || fail "User '$web_owner' does not exist."
    apply_permissions=1
fi

log "Enabling maintenance mode"
maintenance_enabled=1
run_artisan down --retry=60

log "Pulling the latest code from origin/main"
git pull --ff-only origin main

if (( install_composer )); then
    log "Installing production dependencies"
    if command -v composer >/dev/null 2>&1; then
        composer install --no-dev --no-scripts --optimize-autoloader
    else
        php composer.phar install --no-dev --no-scripts --optimize-autoloader
    fi

    log "Discovering Composer packages"
    run_artisan package:discover
fi

if (( run_migrations )); then
    log "Running database migrations"
    run_artisan migrate --force
fi

log "Clearing application caches"
run_artisan optimize:clear

if (( apply_permissions )); then
    log "Setting project ownership to $web_owner:$web_group"
    chown -R "$web_owner:$web_group" .

    log "Setting Laravel writable-directory permissions"
    find storage bootstrap/cache -type d -exec chmod 775 {} +
    find storage bootstrap/cache -type f -exec chmod 664 {} +
fi

log "Disabling maintenance mode"
run_artisan up
maintenance_enabled=0

printf '\nUpdate completed successfully.\n'
