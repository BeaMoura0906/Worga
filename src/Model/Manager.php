<?php

namespace Worga\src\Model;

use Worga\src\Classes\dbConnect;

/**
 * Class Manager
 * Base class for managing database connections and common functionalities.
 */
class Manager 
{
    /**
     * Database connection DSN (Data Source Name).
     * @var string
     */
    private $dsn = 'mysql:host=localhost;dbname=';

    /**
     * Database hostname.
     * @var string
     */
    private $dbhost;

    /**
     * Database name.
     * @var string
     */
    private $dbname;

    /**
     * Database login username.
     * @var string
     */
    private $dblogin;

    /**
     * Database login password.
     * @var string
     */
    private $dbpassword; 

    /**
     * Database manager object.
     * @var dbConnect
     */
    protected $dbManager;

    /**
     * Constructor to initialize the Manager object and establish a database connection.
     */
    public function __construct()
    {
        $this->setEnvVarWithDbCredentials();

        //Configure for the remote server within add .env file to retreive the remote dbname, dblogin and dbpassword
        if( strstr($_SERVER['HTTP_HOST'], $_ENV['DB_HOST'] )){
            $this->dbname = getenv('DB_NAME');
            $this->dblogin = getenv('DB_LOGIN');
            $this->dbpassword = getenv('DB_PASSWORD');
        } else {
            $this->dbname = 'worga';
            $this->dblogin = 'root';
            $this->dbpassword = '';
        }

        // Build DSN for the database connection
        $this->dsn .= $this->dbname . ';charset=utf8';
        
        // Establish a database connection using the dbConnect class
        $this->dbManager = dbConnect::getDb(
            $this->dsn, 
            $this->dblogin, 
            $this->dbpassword
        );
    }

    /**
     * Converts a camelCase string to snake_case. 
     */
    protected function convertCamelCaseToSnakeCase($string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    /**
     * Sets environment variables with database credentials.
     */
    private function setEnvVarWithDbCredentials()
    {
        $envPath = dirname ( __DIR__ ) . '/../.env'; 
        
        if (!file_exists($envPath)) {  
            throw new \Exception('Le fichier .env est introuvable.'); 
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); 
        foreach ($lines as $line) { 
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name); 
            $value = trim($value);

            if (!array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
            }
        }
    }
}