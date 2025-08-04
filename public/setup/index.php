<?php
// A fully interactive, UI-driven setup wizard for Laravel.

// --- CONFIGURATION ---
error_reporting(0);
$basePath = dirname(__DIR__, 2);
$lockFilePath = $basePath . '/storage/installed.lock';
$envPath = $basePath . '/.env';

// --- SECURITY CHECKS ---
$isProduction = false;
if (file_exists($envPath) && strpos(file_get_contents($envPath), 'APP_ENV=production') !== false) {
    $isProduction = true;
}
if (file_exists($lockFilePath)) {
    header('Location: /');
    exit;
}
if ($isProduction) {
    header('HTTP/1.1 503 Service Unavailable');
    die('<h3>Installation Blocked in Production Mode</h3><p>The application is in production mode, but the installation lock file (`storage/installed.lock`) is missing. To protect data, the setup wizard has been disabled. Please contact the server administrator.</p>');
}
if (!file_exists($basePath . '/vendor/autoload.php')) {
    header('HTTP/1.1 503 Service Unavailable');
    die('<h3>Composer Dependencies Missing</h3><p>The `vendor` directory was not found. Please run `composer install` on the command line before proceeding with the web installer.</p>');
}
$envExample = file_get_contents($basePath . '/.env.example');
preg_match('/APP_NAME=(.*)/', $envExample, $appNameMatches);
$appName = $appNameMatches[1] ?? 'My Finance';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Finance - Application Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .step-container {
            display: none;
        }

        .step-container.active {
            display: block;
        }

        #console-output {
            background-color: #000;
            color: #0d0;
            font-family: monospace;
            font-size: 0.8rem;
            height: 200px;
            overflow-y: scroll;
            white-space: pre-wrap;
            border-radius: 5px;
            padding: 10px;
            border: 1px solid #444;
        }
    </style>
</head>

<body class="bg-dark text-white d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="container" style="max-width: 800px;">
        <div class="card bg-dark border-secondary">
            <div class="card-header">
                <h1 class="h3 text-center mb-0">My Finance Setup Wizard 💶</h1>
            </div>
            <div class="card-body p-4">
                <div id="welcome" class="step-container active">
                    <h5 class="card-title text-center mb-4">Welcome to My Finance</h5>
                    <p>This wizard will guide you through the installation process. Before you begin, please make sure you have created an empty database and have your database credentials ready.</p>
                    <div class="d-grid mt-4"><button class="btn btn-primary btn-lg" onclick="showStep('requirements')">Start Setup</button></div>
                </div>
                <div id="requirements" class="step-container">
                    <h5 class="card-title text-center mb-4">Server Requirements</h5>
                    <ul id="requirements-list" class="list-group"></ul>
                    <div class="d-flex justify-content-between mt-4">
                        <button class="btn btn-secondary" onclick="showStep('welcome')">Back</button>
                        <button class="btn btn-primary" id="continue-to-db" disabled onclick="showStep('database')">Continue to Database Setup</button>
                    </div>
                </div>
                <div id="database" class="step-container">
                    <h5 class="card-title text-center mb-4">Database Configuration</h5>
                    <p>Please enter your database connection details below. These will be written to your <code>.env</code> file.</p>
                    <form id="db-form">
                        <div class="row g-3">
                            <div class="col-md-12"><label for="app_name" class="form-label">App Name</label><input type="text" class="form-control" id="app_name" name="app_name" value="<?php echo htmlspecialchars(trim($appName, '"')); ?>" required></div>
                            <div class="col-md-6"><label for="db_host" class="form-label">Database Host</label><input type="text" class="form-control" id="db_host" name="db_host" value="127.0.0.1" required></div>
                            <div class="col-md-6"><label for="db_port" class="form-label">Database Port</label><input type="text" class="form-control" id="db_port" name="db_port" value="3306" required></div>
                            <div class="col-12"><label for="db_database" class="form-label">Database Name</label><input type="text" class="form-control" id="db_database" name="db_database" required></div>
                            <div class="col-md-6"><label for="db_username" class="form-label">Database Username</label><input type="text" class="form-control" id="db_username" name="db_username" required></div>
                            <div class="col-md-6"><label for="db_password" class="form-label">Database Password</label><input type="password" class="form-control" id="db_password" name="db_password"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="showStep('requirements')">Back</button>
                            <button type="submit" class="btn btn-primary" id="submit-db">Save & Test Connection</button>
                        </div>
                    </form>
                </div>
                <div id="install" class="step-container">
                    <h5 class="card-title text-center mb-4">Run Installation</h5>
                    <p>'Your <code>.env</code> file has been created. The next step will generate the application security key, create the database tables, and set up necessary links.</p>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="run_seeder"><label class="form-check-label" for="run_seeder">Run database seeders? (Populates the database with initial/demo data)</label></div>
                    <button class="btn btn-primary w-100" id="run-install-btn" onclick="runInstallation()"><span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span> Run Installation</button>
                    <div id="console-output" class="mt-3">Click "Run Installation" to start...</div>
                    <div class="d-flex justify-content-end mt-4"><button class="btn btn-primary" id="continue-to-admin" style="display:none;" onclick="showStep('admin')">{{ __('Next: Create Admin') }}</button></div>
                </div>
                <div id="admin" class="step-container">
                    <h5 class="card-title text-center mb-4">Create Administrator Account</h5>
                    <form id="admin-form">
                        <div class="row g-3">
                            <div class="col-12"><label for="admin_name" class="form-label">Full Name</label><input type="text" class="form-control" id="admin_name" name="name" required></div>
                            <div class="col-md-6"><label for="admin_email" class="form-label">Email Address</label><input type="email" class="form-control" id="admin_email" name="email" required></div>
                            <div class="col-md-6"><label for="admin_password" class="form-label">Password</label><input type="password" class="form-control" id="admin_password" name="password" required></div>
                        </div>
                        <div class="d-flex justify-content-end mt-4"><button type="submit" class="btn btn-primary" id="submit-admin">Create Admin & Finish</button></div>
                    </form>
                </div>
                <div id="complete" class="step-container">
                    <div class="text-center">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                        <h5 class="card-title mt-3">Installation Complete!</h5>
                        <p class="mt-3">The setup wizard is now locked for security. You can now log in with the admin account you just created.</p>
                        <div class="alert alert-warning mt-3"><strong>Security Warning:</strong> For security reasons, please delete the <code>/public/setup</code> directory from your server.</div>
                        <a href="/" class="btn btn-primary btn-lg mt-3">Go to Application</a>
                    </div>
                </div>
            </div>
            <div id="alert-container" class="card-footer text-center" style="display:none;"></div>
        </div>
    </div>
    <script>
        // START
        let currentStep = 'welcome';

        function showStep(stepId) {
            document.querySelectorAll('.step-container').forEach(el => el.classList.remove('active'));
            document.getElementById(stepId).classList.add('active');
            currentStep = stepId;
            if (stepId === 'requirements') checkRequirements();
        }

        function showAlert(message, type = 'danger') {
            const container = document.getElementById('alert-container');
            container.innerHTML = `<div class="alert alert-${type} mb-0">${message}</div>`;
            container.style.display = 'block';
        }

        function hideAlert() {
            document.getElementById('alert-container').style.display = 'none';
        }

        function setButtonLoading(button, isLoading) {
            const spinner = button.querySelector('.spinner-border');
            button.disabled = isLoading;
            if (spinner) spinner.classList.toggle('d-none', !isLoading);
        }
        async function apiCall(action, data = {}) {
            hideAlert();
            try {
                const formData = new FormData();
                formData.append('action', action);
                for (const key in data) formData.append(key, data[key]);
                const response = await fetch('/setup/api.php', {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                // START: Corrected JS Logic
                // Always return the JSON response, whether it's an error or success.
                // This allows the calling function to inspect the 'data' payload on failure.
                const result = await response.json();
                if (result.error) {
                    showAlert(result.message);
                }
                return result;
                // END: Corrected JS Logic

            } catch (e) {
                showAlert('A fatal error occurred. Please check the Network tab in your browser developer tools for the server response.');
                console.error(e);
                return null; // Return null only on a total network/parsing failure.
            }
        }
        async function checkRequirements() {
            const list = document.getElementById('requirements-list');
            list.innerHTML = '<li>Checking...</li>';
            const result = await apiCall('check_requirements');
            if (!result || result.error) return; // Stop if the call failed or returned a logical error.
            let allOk = true;
            list.innerHTML = '';
            result.data.checks.forEach(item => {
                const icon = item.success ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>';
                if (!item.success) allOk = false;
                list.innerHTML += `<li class="list-group-item bg-dark text-white">${icon} ${item.message}</li>`;
            });
            document.getElementById('continue-to-db').disabled = !allOk;
            if (!allOk) showAlert('One or more requirements are not met. Please fix them and try again.');
        }
        document.getElementById('db-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = document.getElementById('submit-db');
            setButtonLoading(button, true);
            const data = Object.fromEntries(new FormData(this).entries());
            const result = await apiCall('save_env', data);
            if (result && result.success) { // Only proceed on success
                showAlert(result.message, 'success');
                showStep('install');
            }
            setButtonLoading(button, false);
        });
        async function runInstallation() {
            const button = document.getElementById('run-install-btn');
            const consoleOutput = document.getElementById('console-output');
            setButtonLoading(button, true);
            consoleOutput.innerHTML = 'Starting installation...<br>';
            const result = await apiCall('run_install', {
                seed: document.getElementById('run_seeder').checked
            });

            // START
            // This block will now correctly handle the output, even on failure.
            if (!result) { // Handle total network/parsing failure
                setButtonLoading(button, false);
                return;
            }

            // If we have a result, always display the output.
            if (result.data && result.data.output) {
                // The output is now pre-formatted with nl2br and htmlspecialchars on the backend
                consoleOutput.innerHTML = result.data.output;
                consoleOutput.scrollTop = consoleOutput.scrollHeight;
            }

            // If the command succeeded, show the 'next' button.
            if (result.success) {
                showAlert('Installation tasks completed successfully!', 'success');
                document.getElementById('continue-to-admin').style.display = 'block';
            }
            // END

            setButtonLoading(button, false);
        }
        document.getElementById('admin-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = document.getElementById('submit-admin');
            setButtonLoading(button, true);
            const data = Object.fromEntries(new FormData(this).entries());
            const result = await apiCall('create_admin', data);
            if (result && result.success) {
                const finalizeResult = await apiCall('finalize');
                if (finalizeResult && finalizeResult.success) showStep('complete');
            }
            setButtonLoading(button, false);
        });
        // END
    </script>
</body>

</html>