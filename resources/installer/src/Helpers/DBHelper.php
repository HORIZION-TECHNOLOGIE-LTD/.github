<?php

namespace Project\Installer\Helpers;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;
use PDO;
use PDOException;

class DBHelper {

    // MySQL error codes for better error handling
    const MYSQL_ACCESS_DENIED = 1045;
    const MYSQL_CONNECTION_ERROR_1 = 2002;
    const MYSQL_CONNECTION_ERROR_2 = 2003;
    const MYSQL_CONNECTION_ERROR_3 = 2005;
    const MYSQL_CONNECTION_ERROR_4 = 2006;
    const MYSQL_UNKNOWN_DATABASE = 1049;
    const MYSQL_ACCESS_DENIED_DB = 1044;
    const MYSQL_ACCESS_DENIED_TABLE = 1142;

    // Database name validation pattern
    // MySQL allows identifiers to start with: letter (a-z, A-Z), underscore (_), or dollar sign ($)
    // Subsequent characters can be: letters, numbers (0-9), underscores, or dollar signs
    // Note: Dollar signs are valid per MySQL spec but uncommon in practice
    const DB_NAME_PATTERN = '/^[a-zA-Z_$][a-zA-Z0-9_$]*$/';

    /**
     * Test database connection with provided credentials
     * 
     * @param array $data Database connection parameters
     * @return bool
     * @throws Exception If connection fails
     */
    public function testConnection(array $data) {
        $host = $data['host'] ?? '127.0.0.1';
        $port = $data['port'] ?? '3306';
        $dbName = $data['db_name'] ?? '';
        $username = $data['db_user'] ?? '';
        $password = $data['db_user_password'] ?? '';

        // Validate database name to prevent SQL injection
        if (!$this->isValidDatabaseName($dbName)) {
            throw new Exception("Invalid database name. Database name must be 1-64 characters, start with a letter, underscore, or dollar sign, and contain only letters, numbers, underscores, and dollar signs.");
        }

        try {
            // Create DSN (Data Source Name)
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            
            // Try to connect to MySQL server (without specifying database)
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            // Check if database exists using prepared statement
            $stmt = $pdo->prepare("SHOW DATABASES LIKE ?");
            $stmt->execute([$dbName]);
            $databaseExists = $stmt->rowCount() > 0;

            // If database doesn't exist, try to create it
            if (!$databaseExists) {
                // Database name is already validated with isValidDatabaseName(), which ensures
                // it only contains safe characters (alphanumeric, underscore, dollar sign).
                // Backticks are the proper way to quote identifiers in MySQL (not PDO::quote which is for string literals).
                // This is safe because validation prevents any injection attempts.
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

            // Now connect to the specific database to verify full access
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            // Test if we can execute a query (using prepared statement for consistency)
            $stmt = $pdo->prepare("SELECT 1");
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            $errorMessage = "Database connection failed: ";
            
            // Use PDO error codes for reliable error detection
            $errorCode = $e->getCode();
            
            if ($errorCode == self::MYSQL_ACCESS_DENIED) {
                // Access denied
                $errorMessage .= "Invalid username or password.";
            } elseif (in_array($errorCode, [
                self::MYSQL_CONNECTION_ERROR_1,
                self::MYSQL_CONNECTION_ERROR_2,
                self::MYSQL_CONNECTION_ERROR_3,
                self::MYSQL_CONNECTION_ERROR_4
            ])) {
                // Connection errors
                $errorMessage .= "Cannot connect to database server. Please check host and port.";
            } elseif ($errorCode == self::MYSQL_UNKNOWN_DATABASE) {
                // Unknown database
                $errorMessage .= "Database does not exist and could not be created. Please check your permissions.";
            } elseif ($errorCode == self::MYSQL_ACCESS_DENIED_DB || $errorCode == self::MYSQL_ACCESS_DENIED_TABLE) {
                // Access denied to database/table
                $errorMessage .= "Insufficient privileges to create or access the database.";
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            throw new Exception($errorMessage);
        }
    }

    /**
     * Validate database name to prevent SQL injection
     * 
     * @param string $dbName Database name to validate
     * @return bool
     */
    private function isValidDatabaseName($dbName) {
        // MySQL database name rules:
        // - Max 64 characters
        // - Letters (a-z, A-Z), numbers (0-9), dollar signs ($), underscores (_)
        // - Cannot be empty
        if (empty($dbName) || strlen($dbName) > 64) {
            return false;
        }
        
        // Use class constant for validation pattern (see DB_NAME_PATTERN definition above)
        // The pattern enforces: first char must be letter/underscore/dollar, subsequent chars can include numbers
        // This prevents starting with numbers (for better tool compatibility)
        // MySQL technically allows numbers at the start when quoted, but we restrict this for safety
        return preg_match(self::DB_NAME_PATTERN, $dbName) === 1;
    }

    public function create(array $data) {
        // Test connection before saving configuration
        $this->testConnection($data);

        $this->updateEnv([
            'DB_CONNECTION'     => "mysql",
            'DB_HOST'           => $data['host'],
            'DB_PORT'           => $data['port'],
            'DB_DATABASE'       => $data['db_name'],
            'DB_USERNAME'       => $data['db_user'],
            'DB_PASSWORD'       => $data['db_user_password'],
        ]);

        $this->setStepSession();
        $this->saveDataInSession($data);

        $helper = new Helper();
        $helper->cache($data);
    }

    public function updateEnv(array $replace_array) {
        $array_going_to_modify  = $replace_array;
        if (count($array_going_to_modify) == 0) {
            return false;
        }
        $env_file = App::environmentFilePath();
        $env_content = $_ENV;
        $update_array = ["APP_ENV" => App::environment()];
        foreach ($env_content as $key => $value) {
            foreach ($array_going_to_modify as $modify_key => $modify_value) {
                if(!array_key_exists($modify_key,$env_content) && !array_key_exists($modify_key,$update_array)) {
                    $update_array[$modify_key] = $this->setEnvValue($modify_key,$modify_value);
                    break;
                }
                if ($key == $modify_key) {
                    $update_array[$key] = $this->setEnvValue($key,$modify_value);
                    break;
                } else {
                    $update_array[$key] = $this->setEnvValue($key,$value);
                }
            }
        }
        $string_content = "";
        foreach ($update_array as $key => $item) {
            $line = $key . "=" . $item;
            $string_content .= $line . "\n\r";
        }
        sleep(2);
        file_put_contents($env_file, $string_content);
    }

    public function setEnvValue($key,$value) {
        if($key == "APP_KEY") {
            return $value;
        }
        return '"' .$value . '"';
    }

    public function saveDataInSession($data) {
        session()->put('database_config_data',$data);
    }

    public static function getSessionData() {
        return session('database_config_data');
    }

    public function setStepSession() {
        session()->put("database_config","PASSED");
    }

    public static function step($step = 'database_config') {
        return session($step);
    }

    public function migrate() {

        if(App::environment() != "local") {
            $this->updateEnv([
                'APP_ENV'               => "local",
            ]);

            sleep(2);
        }

        self::execute("php artisan migrate:fresh --seed");
        self::execute("php artisan migrate");
        self::execute("php artisan passport:install");

        $this->setMigrateStepSession();

        // $helper = new Helper();
        // $data = cache()->driver("file")->get($helper->cache_key);

        // update env to production
        $this->updateEnv([
            'APP_ENV'               => "production",
        ]);
    }

    public function setMigrateStepSession() {
        session()->put('migrate','PASSED');
    }

    public function updateAccountSettings(array $data) {

        $helper = new Helper();
        $helper->cache($data);

        $p_key = $helper->cache()['product_key'] ?? "";

        if($p_key == "") {
            cache()->driver('file')->forget($helper->cache_key);
            throw new Exception("Something went wrong! Purchase code registration failed! Please try again");
        }

        $admin = DB::table('admins')->first();
        if(!$admin) {
            DB::table('admins')->insert([
                'firstname'     => $data['f_name'],
                'lastname'      => $data['l_name'],
                'password'      => Hash::make($data['password']),
                'email'         => $data['email'],
            ]);
        }else {
            DB::table("admins")->where('id',$admin->id)->update([
                'firstname'     => $data['f_name'],
                'lastname'      => $data['l_name'],
                'password'      => Hash::make($data['password']),
                'email'         => $data['email'],
            ]);
        }

        $validator = new ValidationHelper();

        if($validator->isLocalInstallation() == false) {
            $helper->connection($helper->cache());
        }

        $client_host = parse_url(url('/'))['host'];
        $filter_host = preg_replace('/^www\./', '', $client_host);

        if(Schema::hasTable('script')) {
            DB::table('script')->truncate();
            DB::table('script')->insert([
                'client'        => $filter_host,
                'signature'     => $helper->signature($helper->cache()),
            ]);
        }
        if(Schema::hasTable('basic_settings')) {
            try{
                DB::table('basic_settings')->where('id',1)->update([
                    'site_name'     => $helper->cache()['app_name'] ?? "",
                ]);
            }catch(Exception $e) {
                //handle error
            }
        }

        $db = new DBHelper();

        $db->updateEnv([
            'PRODUCT_KEY'   => $p_key,
            'APP_MODE'      => "live",
            'APP_DEBUG'     => "false"
        ]);

        $this->setAdminAccountStepSession();

        self::execute("php artisan cache:clear");
        self::execute("php artisan config:clear");
    }

    public function setAdminAccountStepSession() {
        session()->put('admin_account','PASSED');
    }

    public static function execute($cmd): string
    {
        $process = Process::fromShellCommandline($cmd);

        $processOutput = '';

        $captureOutput = function ($type, $line) use (&$processOutput) {
            $processOutput .= $line;
        };

        $process->setTimeout(null)
            ->run($captureOutput);

        if ($process->getExitCode()) {
            throw new Exception($cmd . " - " . $processOutput);
        }

        return $processOutput;
    }
}
