# My Finance - Personal Finance Tracker

My Finance is a modern, self-hosted web application built with the Laravel framework to help you manage your personal finances, track expenses, and monitor your budget across different stores and payment methods.

## Features

-   **Transaction Management:** Log all your income and expenses with detailed metadata.
-   **Spreadsheet Mode (Batch Editor):** Inline editing of multiple transactions directly in the table with smart keyboard navigation (Enter key) and real-time modification tracking.
-   **Bulk Record Creation:** A powerful workflow to add multiple records across different days at once with automated focus-jumping for high-speed data entry.
-   **Global Date Picker:** Integrated **Flatpickr** globally for a premium experience, featuring dark mode support, Greek/English localization, and consistent European date formatting (`dd/mm/yyyy`).
-   **Advanced Reporting & Analysis:** Real-time summary cards and detailed monthly/annual reports with responsive layouts optimized for all devices.
-   **Mobile-First Design:** Optimized filter systems and dashboard components ensure a premium experience on small screens.
-   **Data Integrity:** Robust verification modals for batch saves, unsaved changes, and deletion prevention.
-   **Categorization & Entities:** Assign expenses to custom categories and track finances across different stores or entities.
-   **Web-Based Installer:** A simple, guided setup wizard to get you up and running in minutes.

![My Finance Screenshot](public/05.png)

![My Finance Screenshot](public/06.png)

![My Finance Screenshot](public/08.png)

## Requirements

-   PHP >= 8.1
-   Composer
-   MySQL or MariaDB
-   A web server like Apache or Nginx
-   SSH / Terminal access for deployment is highly recommended.

## Installation Guide (Live Server)

This guide covers deploying the application to a live server.

### Step 1: Deploy the Code

Log into your server via SSH.

1.  **Navigate to your web root** (e.g., `/home/user/public_html`). Or in the path you want to install.
2.  **Clone the repository.**

    ```bash
    git clone https://github.com/christoskaterini/my-finance-app.git .
    ```

3.  **Install Production Dependencies.**

    ```bash
    composer install --no-dev --optimize-autoloader
    ```

4.  **Set Server Permissions.**
    ```bash
    find storage bootstrap/cache -type d -exec chmod 775 {} +
    ```
    ```bash
    find storage bootstrap/cache -type f -exec chmod 664 {} +
    ```

### Step 2: Configure the Web Server

For security, your domain's "Document Root" must be set to the `/public` directory inside your project folder.

-   **Example:** If you installed in `/home/user/public_html`, the document root should be `/home/user/public_html/public`.

### Step 3: Run the Web Installer

1.  Open your web browser and navigate to your domain (`http://yourdomain.com`). The application will **automatically redirect you to the setup wizard**.
2.  Follow the on-screen steps to configure the database and create your administrator account. This is the **recommended method** for creating your first user on a live server.

> **Warning:** The seeder creates a user with the email `christoskanotidis@gmail.com` and the password `password`. **Change this password immediately after your first login.**

### Step 4: Handle File Uploads (Manual Storage Link)

The web installer will attempt to create a "storage link" for file uploads. If this fails due to server restrictions, your images may appear broken. You must create this link manually.

1.  Log into your server terminal.
2.  Navigate to your project's **public** directory:
    ```bash
    cd /path/to/your/project/public
    ```
3.  Run the following Linux command to create the link:
    ```bash
    ln -s ../storage/app/public storage
    ```
    This command creates a shortcut named `storage` inside you `public` folder, pointing it to the real storage location.

### Step 5: Final Cleanup (Security)

Once the application is running correctly, you **can delete the installer** for security reasons. (/public/setup)

Your application is now fully installed and secured.

---

## Local Development Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/christoskaterini/my-finance-app.git
    ```
2.  **Navigate into the project:**
    ```bash
    cd my-finance-app
    ```
3.  **Install all dependencies (including dev tools):**
    ```bash
    composer install
    ```
4.  **Create your `.env` file:**
    ```bash
    cp .env.example .env
    ```
5.  **Generate an application key:**
    ```bash
    php artisan key:generate
    ```
6.  **Configure your `.env` file** with your local database details.
7.  **Run migrations and seeders:**
    ```bash
    php artisan migrate --seed
    ```
    -   This will build the database and create a default admin user.

> **Warning:** The seeder creates a user with the email `christoskanotidis@gmail.com` and the password `password`. **Change this password immediately after your first login.**

## Updating the Application (Robust Method)

Choose either the automated script or the manual commands below. Both methods assume you are connected to the server and in the project root.

### Option 1: Update script

Run the script with Bash from the project root:

```bash
bash update.sh
```

A bare `update.sh` is not normally found because the current directory is not part of the shell's command search path. Avoid `chmod +x update.sh` unless the executable bit is committed in Git; otherwise it can make the working tree appear modified.

The script checks that the working tree is clean, enables maintenance mode, and pulls the latest code from `origin main` using a fast-forward-only update. It prompts whether to install Composer dependencies, run database migrations, and update ownership and permissions. It then clears the application caches and disables maintenance mode. If a step fails after maintenance mode is enabled, the script attempts to bring the application back online; if that recovery fails, run `php artisan up` manually.

If you choose to install dependencies, it uses `--no-dev --no-scripts --optimize-autoloader` and runs `php artisan package:discover` afterward. This supports servers where Composer's scripts are restricted by `proc_open`.

If you choose to update permissions, enter the web-server user (or `user:group`) when prompted. The script changes ownership of the project and sets `storage` and `bootstrap/cache` to mode `775`. The account and permissions vary by host; changing ownership may require running the update with sufficient privileges and can affect which account can run future Git updates. For example, a typical web-server account is `www-data`.

### Option 2: Manual update

Run these commands from the project root. Install Composer dependencies only if `composer.json` or `composer.lock` changed, and migrate only when the update includes database migrations.

Enable maintenance mode:

```bash
php artisan down --retry=60
```

Pull the latest code:

```bash
git pull --ff-only origin main
```

Optional — install production dependencies if `composer.json` or `composer.lock` changed:

```bash
composer install --no-dev --no-scripts --optimize-autoloader
```

Discover packages after installing dependencies:

```bash
php artisan package:discover
```

Optional — run pending database migrations:

```bash
php artisan migrate --force
```

Clear application caches:

```bash
php artisan optimize:clear
```

Disable maintenance mode:

```bash
php artisan up
```

If a command fails after maintenance mode is enabled, bring the application back online with `php artisan up` after resolving the issue. If you ran commands as `root`, update ownership and writable-directory permissions as needed, replacing `www-data` with your web-server user or `user:group`:

```bash
chown -R www-data:www-data .
```

```bash
find storage bootstrap/cache -type d -exec chmod 775 {} +
```

```bash
find storage bootstrap/cache -type f -exec chmod 664 {} +
```

## Managing Dependencies (3 Methods)

If you add new packages via `composer require` locally and need to deploy them to the server:

### Method 1: On-Server with `--no-scripts` (Recommended)
This avoids `proc_open` errors while letting Composer handle downloading and autoloading on the server:
```bash
composer install --no-dev --no-scripts --optimize-autoloader
```

Then run package discovery:

```bash
php artisan package:discover
```

### Method 2: Manual `vendor` Upload (From Local Dev / Laragon)
If you prefer not running Composer on the production server:
1. Install packages locally:
   ```bash
   composer require <package-name>
   ```
2. Commit and push `composer.json` and `composer.lock` to Git.
3. Zip your local `vendor` folder (`vendor.zip`).
4. Upload `vendor.zip` via SFTP / MobaXterm to your project root on the server.
5. Extract it (replacing the server's `vendor/` directory).
6. Fix permissions:
   ```bash
   chown -R santempougatsa:santempougatsa vendor/
   ```
7. Run cache clear:
   ```bash
   php artisan optimize:clear
   ```

### Method 3: Enable `proc_open` in CLI PHP Configuration
If you want standard `composer install` to run on the server without any extra flags:
1. Find your CLI `php.ini` path:
   ```bash
   php --ini | grep "Loaded Configuration"
   ```
   *(e.g., `/etc/php/8.2/cli/php.ini` or `/etc/php/8.3/cli/php.ini`)*
2. Open that file and find the `disable_functions` line.
3. Remove `proc_open` from the `disable_functions` list and save.
4. Now standard production dependency installation should execute without the `proc_open` restriction:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

## Email settings

#### Set your email credentials in the .env file

In next update will be in the Settings/General page

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
