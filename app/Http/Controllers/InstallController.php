<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstallController extends Controller
{
    /**
     * Display installation welcome page
     */
    public function index()
    {
        // Check if already installed
        if ($this->isAlreadyInstalled()) {
            return redirect()->route('index')->with('error', 'Application is already installed.');
        }

        return view('install.welcome');
    }

    /**
     * Process installation
     */
    public function install(Request $request)
    {
        // Check if already installed
        if ($this->isAlreadyInstalled()) {
            return response()->json([
                'success' => false,
                'message' => 'Application is already installed.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'db_host' => 'required|string',
            'db_port' => 'required|integer',
            'db_name' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Step 1: Backup existing .env if it exists
            $envPath = base_path('.env');
            if (File::exists($envPath)) {
                $backupPath = base_path('.env.backup.' . time());
                File::copy($envPath, $backupPath);
            }

            // Step 2: Update .env file with database credentials
            $this->updateEnvFile([
                'APP_NAME' => $request->app_name,
                'APP_URL' => $request->app_url,
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_name,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password,
            ]);

            // Step 3: Generate APP_KEY
            Artisan::call('key:generate', ['--force' => true]);

            // Step 4: Test database connection
            config([
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_name,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password,
            ]);

            DB::reconnect();
            DB::connection()->getPdo();

            // Step 5: Run migrations
            Artisan::call('migrate', ['--force' => true]);

            // Step 6: Create admin user
            $adminUser = DB::table('admins')->insert([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Step 7: Create storage link
            try {
                Artisan::call('storage:link');
            } catch (Exception $e) {
                // Storage link might already exist, continue
            }

            // Step 8: Mark installation as complete
            $this->updateEnvFile([
                'APP_INSTALLED' => 'true',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Installation completed successfully!'
            ]);

        } catch (Exception $e) {
            // Restore backup if exists
            if (isset($backupPath) && File::exists($backupPath)) {
                File::copy($backupPath, $envPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if application is already installed
     */
    protected function isAlreadyInstalled(): bool
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return false;
        }

        $envContent = File::get($envPath);
        
        // Check for APP_INSTALLED=true
        if (preg_match('/^APP_INSTALLED\s*=\s*true/m', $envContent)) {
            return true;
        }

        // Check for non-empty APP_KEY
        if (preg_match('/^APP_KEY\s*=\s*base64:.+/m', $envContent)) {
            return true;
        }

        return false;
    }

    /**
     * Update .env file with new values
     */
    protected function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        foreach ($data as $key => $value) {
            $escapedValue = $this->escapeEnvValue($value);
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $envContent)) {
                // Update existing key
                $envContent = preg_replace($pattern, "{$key}={$escapedValue}", $envContent);
            } else {
                // Add new key
                $envContent .= "\n{$key}={$escapedValue}";
            }
        }

        File::put($envPath, $envContent);
    }

    /**
     * Escape environment variable value
     */
    protected function escapeEnvValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // If value contains spaces or special characters, wrap in quotes
        if (preg_match('/[\s\'"#$&\(\)]/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }
}
