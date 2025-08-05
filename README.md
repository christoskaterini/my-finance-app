# My Finance - Personal Finance Tracker

My Finance is a modern, self-hosted web application built with the Laravel framework to help you manage your personal finances, track expenses, and monitor your budget across different stores and payment methods.

## Features

-   **Transaction Management:** Log all your income and expenses with details.
-   **Categorization:** Assign expenses to custom categories.
-   **Multi-Store Support:** Track finances across different stores or entities.
-   **Payment Methods:** Define and use various payment methods (Cash, Credit Card, etc.).
-   **Dynamic Settings:** Customize currency, language, and timezone through a user-friendly settings panel.
-   **Web-Based Installer:** A simple, guided setup wizard to get you up and running in minutes.

![My Finance Screenshot](public/05.png)

![My Finance Screenshot](public/06.png)

![My Finance Screenshot](public/08.png)

---

## Requirements

-   PHP >= 8.1
-   Composer
-   MySQL or MariaDB
-   A web server like Apache or Nginx
-   SSH / Terminal access for deployment is highly recommended.

---

## Installation Guide

This guide covers deploying the application to a live server. For local development, the steps are similar but you can use `php artisan serve`.

### Step 1: Deploy the Code

Log into your server via SSH.

1.  **Navigate to your web root** (e.g., `/home/user/public_html`).
2.  **Clone the repository.** This downloads the application code.

    ```bash
    git clone https://github.com/christoskaterini/my-finance-app.git .
    ```

    _(Note: If the repository is private, you will be asked for your GitHub username and a Personal Access Token for the password.)_

3.  **Install Production Dependencies.** This is a critical step that installs only the essential libraries required for the application to run.

    ```bash
    composer install --no-dev --optimize-autoloader
    ```

4.  **Set Server Permissions.** The web server needs permission to write to certain folders.
    ```bash
    chmod -R 775 storage bootstrap/cache
    ```

### Step 2: Configure the Web Server

For security and proper functioning, your domain's "Document Root" must be set to the `/public` directory inside your project folder.

-   **Example:** If you installed in `/home/user/public_html`, the document root should be `/home/user/public_html/public`.

This is a standard security practice for all Laravel applications.

### Step 3: Run the Web Installer

1.  Open your web browser and navigate to `http://yourdomain.com/setup`.
2.  Follow the on-screen wizard. It will:
    -   Check server requirements.
    -   Ask for your database credentials.
    -   Create the `.env` environment file.
    -   Run all necessary database migrations.
    -   Create the storage link for file uploads.
    -   Prompt you to create your main administrator account.

### Step 4: Final Cleanup (Security)

Once you have confirmed the application is running correctly, you **must delete the installer** for security reasons.

1.  Log back into your server terminal.
2.  Navigate to your project folder.
3.  Run the following command:
    ```bash
    rm -rf public/setup
    ```

Your application is now fully installed, configured, and secured.

---

## Updating the Application

Updating your application after making changes locally is a simple and reliable process.

1.  First, make your changes on your local machine, and then commit and push them to your GitHub repository's `main` branch.

    ```bash
    # On your local machine
    git commit -am "Add new feature or fix bug"
    git push origin main
    ```

2.  Next, log in to your server via SSH and run the following commands from your project's root directory. This is your "update cheat sheet":

        ```bash
        # Navigate to your project directory on the server
        cd /path/to/my-finance-app

        # Pull the latest code changes from GitHub
        git pull origin main

        # Install any new or updated packages
        composer install --no-dev --optimize-autoloader

        # Run any new database migrations
        php artisan migrate --force

        # Clear cached files to ensure your new code is used
        php artisan optimize:clear
        ```

    Your application is now updated to the latest version.

---
