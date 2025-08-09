<?php
// A fully interactive, UI-driven setup wizard for Laravel.

// --- CONFIGURATION ---
error_reporting(0);
$basePath = dirname(__DIR__, 2);
$lockFilePath = $basePath . '/storage/installed.lock';
$envPath = $basePath . '/.env';

// --- SECURITY CHECKS ---
$lockFilePathCheck = __DIR__ . '/../storage/installed.lock';
if (! file_exists($lockFilePathCheck) && strpos($_SERVER['REQUEST_URI'], '/setup') === false) {
    header('Location: /setup');
    exit;
}
if (file_exists($lockFilePath)) {
    header('Location: /');
    exit;
}
if (!file_exists($basePath . '/vendor/autoload.php')) {
    die('<h3>Composer Dependencies Missing</h3><p>Please run `composer install` on the command line before proceeding.</p>');
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
                        <button type="button" class="btn btn-secondary" onclick="showStep('welcome')">Back</button>
                        <button class="btn btn-primary" id="continue-to-db" disabled onclick="showStep('database')">Continue to Database Setup</button>
                    </div>
                </div>
                <div id="database" class="step-container">
                    <h5 class="card-title text-center mb-4">Database Configuration</h5>
                    <p>Please enter your database connection details below.</p>
                    <form id="db-form">
                        <div class="row g-3">
                            <div class="col-md-6"><label for="db_host" class="form-label">Database Host</label><input type="text" class="form-control" id="db_host" name="db_host" value="127.0.0.1" required></div>
                            <div class="col-md-6"><label for="db_port" class="form-label">Database Port</label><input type="text" class="form-control" id="db_port" name="db_port" value="3306" required></div>
                            <div class="col-12"><label for="db_database" class="form-label">Database Name</label><input type="text" class="form-control" id="db_database" name="db_database" required></div>
                            <div class="col-md-6"><label for="db_username" class="form-label">Database Username</label><input type="text" class="form-control" id="db_username" name="db_username" required></div>
                            <div class="col-md-6"><label for="db_password" class="form-label">Database Password</label><input type="password" class="form-control" id="db_password" name="db_password"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="showStep('requirements')">Back</button>
                            <button type="submit" class="btn btn-primary" id="submit-db">Test Connection & Continue</button>
                        </div>
                    </form>
                </div>
                <div id="app-settings" class="step-container">
                    <h5 class="card-title text-center mb-4">Application & Mail Settings</h5>
                    <form id="app-form">
                        <h6 class="text-info">Application Settings</h6>
                        <hr class="text-secondary">
                        <div class="row g-3 mb-4">
                            <div class="col-12"><label for="app_name" class="form-label">App Name (You can leave this)</label><input type="text" class="form-control" id="app_name" name="app_name" value="My Finance" required></div>
                            <div class="col-12"><label for="app_url" class="form-label">App URL</label><input type="url" class="form-control" id="app_url" name="app_url" required placeholder="https://yourdomain.com"></div>
                            <div class="col-md-6">
                                <label for="app_env" class="form-label">App Environment (⚠️Choose Local only if you test in local env)</label>
                                <select id="app_env" name="app_env" class="form-select">
                                    <option value="production">Production</option>
                                    <option value="local" selected>Local</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check"><input class="form-check-input" type="checkbox" id="app_debug" name="app_debug"><label class="form-check-label" for="app_debug">Enable Debug Mode</label></div>
                            </div>
                        </div>

                        <h6 class="text-info">Mail Configuration (for password resets, etc.)</h6>
                        <hr class="text-secondary">
                        <div class="row g-3">
                            <div class="col-md-6"><label for="mail_mailer" class="form-label">Mail Driver</label><input type="text" class="form-control" id="mail_mailer" name="mail_mailer" value="smtp" placeholder="e.g., smtp, log"></div>
                            <div class="col-md-6"><label for="mail_host" class="form-label">SMTP Host</label><input type="text" class="form-control" id="mail_host" name="mail_host" placeholder="smtp.mailgun.org, mail.mydomain.com etc"></div>
                            <div class="col-md-6"><label for="mail_port" class="form-label">SMTP Port</label><input type="text" class="form-control" id="mail_port" name="mail_port" placeholder="default 2525, tls 587,ssl 465"></div>
                            <div class="col-md-6"><label for="mail_encryption" class="form-label">Encryption</label><input type="text" class="form-control" id="mail_encryption" name="mail_encryption" placeholder="ssl for 465, tls for 587, etc. "></div>
                            <div class="col-12"><label for="mail_username" class="form-label">SMTP Username</label><input type="text" class="form-control" id="mail_username" name="mail_username" placeholder="Your SMTP username"></div>
                            <div class="col-12"><label for="mail_password" class="form-label">SMTP Password</label><input type="password" class="form-control" id="mail_password" name="mail_password" placeholder="Your SMTP password"></div>
                            <div class="col-md-6"><label for="mail_from_address" class="form-label">From: Email Address</label><input type="email" class="form-control" id="mail_from_address" name="mail_from_address" placeholder="noreply@yourdomain.com"></div>
                            <div class="col-md-6"><label for="mail_from_name" class="form-label">From: Name</label><input type="text" class="form-control" id="mail_from_name" name="mail_from_name" placeholder="My Finance App"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" onclick="showStep('database')">Back</button>
                            <button type="submit" class="btn btn-primary" id="submit-all">Save Settings & Install</button>
                        </div>
                    </form>
                </div>
                <div id="install" class="step-container">
                    <h5 class="card-title text-center mb-4">Running Installation...</h5>
                    <p>The installer is now running key generation, database migration, and other setup tasks. This may take a moment. Please do not close this window.</p>
                    <div id="console-output" class="mt-3">Starting installation...</div>
                    <div class="d-flex justify-content-end mt-4"><button class="btn btn-primary" id="continue-to-admin" style="display:none;" onclick="showStep('admin')">Next: Create Admin</button></div>
                </div>
                <div id="admin" class="step-container">
                    <h5 class="card-title text-center mb-4">Create Administrator Account</h5>
                    <p>Finally, create your admin user account to log in to the application.</p>
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
        // START: Corrected Javascript Logic
        let currentStep = 'welcome';

        function showStep(stepId) {
            document.querySelectorAll('.step-container').forEach(el => el.classList.remove('active'));
            document.getElementById(stepId).classList.add('active');
            currentStep = stepId;
            if (stepId === 'requirements') checkRequirements();
            if (stepId === 'app-settings') {
                document.getElementById('app_url').value = window.location.protocol + '//' + window.location.host;
                const envSelect = document.getElementById('app_env');
                const debugCheck = document.getElementById('app_debug');
                const appNameInput = document.getElementById('app_name');

                // Auto-check debug if environment is local
                envSelect.addEventListener('change', function() {
                    debugCheck.checked = this.value === 'local';
                });
                // Set initial state
                debugCheck.checked = envSelect.value === 'local';
                // Use the app name from the DB form
                appNameInput.value = document.querySelector('#db-form #app_name').value;
            }
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
            const spinner = button.querySelector('.spinner-border'); // Assumes a spinner exists, which might not be the case
            button.disabled = isLoading;
            if (isLoading) {
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = 'Processing...';
            } else if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
            }
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

                const result = await response.json();
                if (result.error) {
                    showAlert(result.message);
                }
                return result;

            } catch (e) {
                showAlert('A fatal error occurred. Please check the Network tab in your browser developer tools for the server response.');
                console.error(e);
                return null;
            }
        }

        async function checkRequirements() {
            const list = document.getElementById('requirements-list');
            list.innerHTML = '<li>Checking...</li>';
            const result = await apiCall('check_requirements');
            if (!result || result.error) return;
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

        // 1. Listen for the Database form submission
        document.getElementById('db-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = document.getElementById('submit-db');
            setButtonLoading(button, true);

            const dbData = Object.fromEntries(new FormData(this).entries());
            const result = await apiCall('test_db', dbData);

            if (result && result.success) {
                showAlert(result.message, 'success');
                showStep('app-settings'); // On success, show the next step
            }
            setButtonLoading(button, false);
        });


        // 2. Listen for the final App & Mail form submission
        document.getElementById('app-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const button = document.getElementById('submit-all');
            setButtonLoading(button, true);

            // Combine data from both forms to send at once
            const dbData = Object.fromEntries(new FormData(document.getElementById('db-form')).entries());
            const appData = Object.fromEntries(new FormData(this).entries());
            const allData = {
                ...dbData,
                ...appData
            };
            allData.app_debug = document.getElementById('app_debug').checked;

            const result = await apiCall('save_env', allData);
            if (result && result.success) {
                showAlert(result.message, 'success');
                runInstallation(); // Immediately start the installation process
            } else {
                // Only stop the loading animation if there's an error
                setButtonLoading(button, false);
            }
        });

        async function runInstallation() {
            showStep('install'); // Switch to the installation console view
            const consoleOutput = document.getElementById('console-output');

            // Note: run_seeder checkbox is no longer on this screen, so we don't pass it.
            // If you want to keep the seeder option, the checkbox needs to be on the 'app-settings' page.
            const result = await apiCall('run_install');

            if (!result) return;
            if (result.data && result.data.output) {
                consoleOutput.innerHTML = result.data.output;
                consoleOutput.scrollTop = consoleOutput.scrollHeight;
            }
            if (result.success) {
                showAlert('Installation tasks completed successfully!', 'success');
                document.getElementById('continue-to-admin').style.display = 'block';
            }
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
        // END: Corrected Javascript Logic
    </script>
</body>

</html>