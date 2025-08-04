# My Finance - Personal Finance Tracker

My Finance is a modern, self-hosted web application built with the Laravel framework to help you manage your personal finances, track expenses, and monitor your budget across different stores and payment methods.

![My Finance Screenshot](public/setup-screenshot.png)
_(Suggestion: Replace this with a real screenshot of your app's dashboard once it's running!)_

## Features

-   **Transaction Management:** Log all your income and expenses with details.
-   **Categorization:** Assign expenses to custom categories.
-   **Multi-Store Support:** Track finances across different stores or entities.
-   **Payment Methods:** Define and use various payment methods (Cash, Credit Card, etc.).
-   **Dynamic Settings:** Customize currency, language, and timezone through a user-friendly settings panel.
-   **Web-Based Installer:** A simple, guided setup wizard to get you up and running in minutes.

---

## Requirements

-   PHP >= 8.1
-   Composer
-   MySQL or MariaDB
-   BCMath PHP Extension
-   Ctype PHP Extension
-   Fileinfo PHP Extension
-   JSON PHP Extension
-   Mbstring PHP Extension
-   OpenSSL PHP Extension
-   PDO PHP Extension
-   Tokenizer PHP Extension
-   XML PHP Extension

---

## Local Development Setup

Follow these steps to set up the project on your local machine (like XAMPP, WAMP, or Laravel Herd).

1.  **Clone the Repository**

    ```bash
    git clone https://github.com/your-username/my-finance-app.git
    cd my-finance-app
    ```

2.  **Install Dependencies**
    This command installs all necessary libraries, including development tools like Faker.

    ```bash
    composer install
    ```

3.  **Create Your Environment File**
    Copy the example environment file.

    ```bash
    cp .env.example .env
    ```

4.  **Generate Application Key**
    This is a crucial security step.

    ```bash
    php artisan key:generate
    ```

5.  **Configure `.env` File**
    Open the `.env` file and set up your local database credentials.

    ```dotenv
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=my_finance_local
    DB_USERNAME=root
    DB_PASSWORD=
    ```

6.  **Run Database Migrations & Seeders**
    This command builds the database structure and fills it with sample data for testing.

    ```bash
    php artisan migrate --seed
    ```

7.  **Create Storage Link**
    This makes your uploaded files publicly accessible.

    ```bash
    php artisan storage:link
    ```

8.  **Start the Development Server**
    ```bash
    php artisan serve
    ```
    Your application will be available at `http://127.0.0.1:8000`.

---

## Deployment to a Live Server

Follow these instructions to deploy the application to a live hosting environment (e.g., cPanel, DigitalOcean, etc.). This guide assumes you have SSH/terminal access.

### Method 1: Deployment via Git (Recommended)

1.  **Clone the Repository on the Server**
    Log into your server via SSH and navigate to the directory where you want to install the app (e.g., `public_html`).

    ```bash
    git clone https://github.com/your-username/my-finance-app.git .
    ```

2.  **Install Production Dependencies**
    This is the most important step. It installs only the essential libraries and skips development tools like Faker.

    ```bash
    composer install --no-dev --optimize-autoloader
    ```

3.  **Set File Permissions**
    The web server needs to be able to write to certain directories.

    ```bash
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
    ```

    _(Note: The user `www-data` might be different depending on your server, e.g., `apache` or your own username.)_

4.  **Run the Web Installer**
    There is no need to manually create the `.env` file or run migrations. The web installer will handle everything.

    -   Navigate your browser to `http://yourdomain.com/setup`.
    -   Follow the on-screen instructions to check requirements, set up your database, and create your admin user.
    -   The installer will automatically run the necessary commands like `migrate` and `storage:link`.

5.  **Configure Your Web Server**
    Point your domain's document root to the `/public` directory of your Laravel installation. This is a critical security measure.

6.  **(Highly Recommended) Secure Your Installation**
    After the setup is complete and you have verified the application is working, delete the installer for security.
    ```bash
    rm -rf public/setup
    ```

### Method 2: Deployment via File Upload (Copy/Paste)

If you don't have Git or SSH access, you can deploy by uploading the files.

1.  **Prepare Files Locally**
    On your local machine, run the command to prepare your `vendor` folder for production. This removes all development packages.

    ```bash
    composer install --no-dev --optimize-autoloader
    ```

2.  **Upload Project Files**
    Upload all project files and folders **except for the `.env` file**.

3.  **Run the Web Installer**
    As in the method above, navigate to `http://yourdomain.com/setup` and follow the on-screen steps. The installer will create the `.env` file and set up the application for you.

4.  **Secure Your Installation**
    Once setup is complete, delete the `public/setup` directory via your file manager or FTP client.

---

## Useful Artisan Commands

Here are some commands you might find useful for maintaining your application via the terminal.

-   **Clear All Caches:** Run this after making configuration changes.
    ```bash
    php artisan optimize:clear
    ```
-   **Run Migrations:** Apply any new database changes.
    ```bash
    php artisan migrate --force
    ```
-   **Force Create Storage Link:** If the installer fails due to permissions, you can run this command manually.
    ```bash
    php artisan storage:link
    ```
-   **Enter Tinker:** A powerful interactive REPL for your application.
    ```bash
    php artisan tinker
    ```
