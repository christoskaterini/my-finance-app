<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;

class SetupController extends Controller
{
    /**
     * Show the setup wizard homepage.
     */
    public function index()
    {
        $requirements = $this->checkRequirements();
        $allRequirementsMet = !in_array(false, array_column($requirements, 'check'));

        return view('setup.index', [
            'step' => 1,
            'requirements' => $requirements,
            'allRequirementsMet' => $allRequirementsMet,
        ]);
    }

    /**
     * Save the database credentials to the .env file.
     */
    public function saveDatabase(Request $request)
    {
        $credentials = $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        // Try to connect to the database with the provided credentials
        config([
            'database.connections.mysql_test' => [
                'driver' => 'mysql',
                'host' => $credentials['db_host'],
                'port' => $credentials['db_port'],
                'database' => $credentials['db_database'],
                'username' => $credentials['db_username'],
                'password' => $credentials['db_password'],
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);
        
        try {
            DB::connection('mysql_test')->getPdo();
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Could not connect to the database. Please check your credentials and try again.');
        }

        // If connection is successful, update the .env file
        $this->updateEnvFile([
            'DB_HOST' => $credentials['db_host'],
            'DB_PORT' => $credentials['db_port'],
            'DB_DATABASE' => $credentials['db_database'],
            'DB_USERNAME' => $credentials['db_username'],
            'DB_PASSWORD' => $credentials['db_password'] ? '"'.$credentials['db_password'].'"' : '',
        ]);

        return view('setup.index', ['step' => '2b_ask_mail']);
    }

    public function saveMail(Request $request)
    {
        $credentials = $request->validate([
            'mail_mailer' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
        ]);

        $this->updateEnvFile([
            'MAIL_MAILER' => $credentials['mail_mailer'],
            'MAIL_HOST' => $credentials['mail_host'],
            'MAIL_PORT' => $credentials['mail_port'],
            'MAIL_USERNAME' => '"'.$credentials['mail_username'].'"',
            'MAIL_PASSWORD' => '"'.$credentials['mail_password'].'"',
            'MAIL_ENCRYPTION' => $credentials['mail_encryption'],
            'MAIL_FROM_ADDRESS' => $credentials['mail_from_address'],
            'MAIL_FROM_NAME' => '"${APP_NAME}"',
        ]);
        
        Artisan::call('config:clear');

        return view('setup.index', ['step' => 3]);
    }

    /**
     * Run the database migrations and seeders.
     */
    public function runMigrations()
    {
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
        } catch (\Exception $e) {
            return back()->with('error', 'Could not run migrations. Error: ' . $e->getMessage());
        }

        return view('setup.index', ['step' => '3b_ask_seeder']);
    }

    public function runSeeder(Request $request)
    {
        if ($request->input('seed_data') === 'yes') {
            try {
                Artisan::call('db:seed', [
                    '--class' => 'DefaultDataSeeder',
                    '--force' => true
                ]);
            } catch (\Exception $e) {
                return back()->with('error', 'Could not run the seeder. Error: ' . $e->getMessage());
            }
        }
        
        return view('setup.index', ['step' => 4]);
    }

    /**
     * Create the super admin user and finalize the installation.
     */
    public function createAdmin(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', // Explicitly set the role to 'admin'
        ]);

        // Create the installation lock file
        File::put(storage_path('installed.lock'), 'Installation complete on ' . now());

        return view('setup.index', ['step' => 5]);
    }

    /**
     * Helper function to check server requirements.
     */
    private function checkRequirements(): array
    {
        return [
            ['name' => 'PHP Version >= 8.1', 'check' => version_compare(PHP_VERSION, '8.1', '>=')],
            ['name' => 'BCMath PHP Extension', 'check' => extension_loaded('bcmath')],
            ['name' => 'Ctype PHP Extension', 'check' => extension_loaded('ctype')],
            ['name' => 'JSON PHP Extension', 'check' => extension_loaded('json')],
            ['name' => 'Mbstring PHP Extension', 'check' => extension_loaded('mbstring')],
            ['name' => 'PDO PHP Extension', 'check' => extension_loaded('pdo')],
            ['name' => 'Writable /storage directory', 'check' => is_writable(storage_path())],
            ['name' => 'Writable /bootstrap/cache directory', 'check' => is_writable(base_path('bootstrap/cache'))],
        ];
    }

    /**
     * Helper function to update the .env file.
     */
    private function updateEnvFile(array $data)
    {
        $envFilePath = base_path('.env');
        $content = File::get($envFilePath);

        foreach ($data as $key => $value) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        }

        File::put($envFilePath, $content);
    }
}