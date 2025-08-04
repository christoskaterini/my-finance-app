<?php

// START

// API backend for the interactive setup wizard. (v8 - Final with Seeder Safety Check)
// Checks if Faker class exists before attempting to seed.

// --- CONFIGURATION & BOOTSTRAP ---
@ini_set('display_errors', 0);
header('Content-Type: application/json');

$basePath = dirname(__DIR__, 2);
$lockFilePath = $basePath . '/storage/installed.lock';
$envPath = $basePath . '/.env';

// --- HELPER FUNCTIONS ---
function send_json_response($data) {
    while (ob_get_level() > 0) ob_end_clean();
    echo json_encode($data);
    exit;
}

function send_error($message, $data = []) {
    send_json_response(['success' => false, 'error' => true, 'message' => $message, 'data' => $data]);
}

function send_success($message, $data = []) {
    send_json_response(['success' => true, 'error' => false, 'message' => $message, 'data' => $data]);
}

function bootstrap_laravel() {
    global $basePath;
    try {
        if (!file_exists($basePath . '/vendor/autoload.php')) return 'Composer dependencies not found.';
        require_once $basePath . '/vendor/autoload.php';
        $app = require_once $basePath . '/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    } catch (\Throwable $e) {
        return 'LARAVEL BOOTSTRAP FAILED: ' . $e->getMessage() . ' in file ' . $e->getFile() . ' on line ' . $e->getLine();
    }
}

// --- SECURITY CHECKS ---
if (file_exists($lockFilePath)) send_error('Application is already installed.');

// --- ACTION ROUTER ---
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'check_requirements':
        $checks = [];
        $checks[] = ['message' => 'PHP Version >= 8.1.0 (' . PHP_VERSION . ' detected)', 'success' => version_compare(PHP_VERSION, '8.1.0', '>=')];
        $required_extensions = ['Ctype', 'Fileinfo', 'JSON', 'Mbstring', 'OpenSSL', 'PDO', 'Tokenizer', 'XML'];
        foreach ($required_extensions as $ext) $checks[] = ['message' => "PHP Extension: {$ext}", 'success' => extension_loaded($ext)];
        $writable_dirs = ['storage', 'bootstrap/cache'];
        foreach ($writable_dirs as $dir) {
            $test_file = $basePath . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . '_installer_test.tmp';
            $is_writable = @file_put_contents($test_file, 'test') !== false;
            if ($is_writable) @unlink($test_file);
            $checks[] = ['message' => "Directory is writable: <code>{$dir}</code>", 'success' => $is_writable];
        }
        $checks[] = ['message' => 'PHP function <code>symlink</code> is available', 'success' => function_exists('symlink')];
        send_success('Requirements checked.', ['checks' => $checks]);
        break;

    case 'save_env':
        $envTemplatePath = $basePath . '/.env.example';
        if (!is_readable($envTemplatePath)) send_error('Could not read .env.example file.');
        $envTemplate = file_get_contents($envTemplatePath);
        $envTemplate = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $envTemplate);
        $dbSettings = [ 'DB_HOST' => $_POST['db_host'] ?? '127.0.0.1', 'DB_PORT' => $_POST['db_port'] ?? '3306', 'DB_DATABASE' => $_POST['db_database'] ?? '', 'DB_USERNAME' => $_POST['db_username'] ?? '', 'DB_PASSWORD' => $_POST['db_password'] ?? '', ];
        foreach ($dbSettings as $key => $value) { $envTemplate = preg_replace('/^#?\s*' . $key . '=.*/m', $key . '=' . $value, $envTemplate); }
        $envTemplate = preg_replace('/^APP_NAME=.*/m', 'APP_NAME="' . ($_POST['app_name'] ?? 'My Finance') . '"', $envTemplate);
        try {
            $dsn = "mysql:host={$dbSettings['DB_HOST']};port={$dbSettings['DB_PORT']};dbname={$dbSettings['DB_DATABASE']}";
            new PDO($dsn, $dbSettings['DB_USERNAME'], $dbSettings['DB_PASSWORD'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (\PDOException $e) { send_error('Database connection failed: ' . htmlspecialchars($e->getMessage())); }
        if (file_put_contents($envPath, $envTemplate) === false) send_error('Could not write to .env file.');
        send_success('.env file created and database connection successful.');
        break;

    case 'run_install':
        $output = "Attempting to start Laravel...\n";
        $app = bootstrap_laravel();
        if (is_string($app)) {
            $output .= "\n" . $app;
            send_error("A fatal error occurred while starting the application.", ['output' => nl2br(htmlspecialchars($output))]);
        }
        $output .= "Laravel started successfully.\n\n";

        try {
            $output .= "Generating application key...\n";
            \Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
            $output .= \Illuminate\Support\Facades\Artisan::output();
            $output .= "\nRunning Migrations...\n";
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output .= \Illuminate\Support\Facades\Artisan::output();
            
            // START: This is the final bug fix
            if (isset($_POST['seed']) && $_POST['seed'] === 'true') {
                $output .= "\nRunning Seeders...\n";
                if (class_exists(\Faker\Factory::class)) {
                    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                    $output .= \Illuminate\Support\Facades\Artisan::output();
                } else {
                    $output .= "Skipped. The Faker library was not found.\nTo run seeders, please install with dev dependencies (`composer install`).\n";
                }
            }
            // END: This is the final bug fix

            $output .= "\nCreating storage link...\n";
            $link = $basePath . '/public/storage'; $target = $basePath . '/storage/app/public';
            if (file_exists($link)) { $output .= "Storage link already exists.\n"; } 
            elseif (function_exists('symlink')) { symlink($target, $link); $output .= "Storage link created successfully.\n"; } 
            else { $output .= "Warning: `symlink` function is disabled. Please create the storage link manually.\n"; }
        } catch (\Throwable $e) {
            $output .= "\n\nAN ARTISAN ERROR OCCURRED:\n" . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
            send_error("An error occurred during Artisan commands.", ['output' => nl2br(htmlspecialchars($output))]);
        }
        send_success('Installation tasks completed.', ['output' => nl2br(htmlspecialchars($output))]);
        break;

    case 'create_admin':
        $app = bootstrap_laravel();
        if (is_string($app)) send_error('Failed to bootstrap Laravel to create user: ' . $app);
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['password'])) send_error('All admin user fields are required.');
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) send_error('Invalid email format.');
        try {
            $user = \App\Models\User::create(['name' => $_POST['name'], 'email' => $_POST['email'], 'password' => \Illuminate\Support\Facades\Hash::make($_POST['password']), 'role' => 'admin']);
            send_success('Admin user created successfully.', ['user_id' => $user->id]);
        } catch (\Throwable $e) { send_error('Could not create admin user: ' . htmlspecialchars($e->getMessage())); }
        break;

    case 'finalize':
        $app = bootstrap_laravel();
        if (is_string($app)) send_error('Failed to bootstrap Laravel to finalize: ' . $app);
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        if (file_put_contents($lockFilePath, 'Installation completed on ' . date('Y-m-d H:i:s')) === false) send_error('Could not create lock file.');
        send_success('Installation finalized and locked.');
        break;

    default:
        send_error('Invalid action specified.');
        break;
}
// END