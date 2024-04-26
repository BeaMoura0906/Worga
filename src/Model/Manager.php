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

        //Configure for the remote server within add .env file to retreive the remote dbname, dblogin and dbpassword
        /*
        if( strstr($_SERVER['HTTP_HOST'], '') ){
            $this->dbname = '';
            $this->dblogin = '';
            $this->dbpassword = '';
        } else {
            $this->dbname = 'worga';
            $this->dblogin = 'root';
            $this->dbpassword = '';
        }
        */

        $this->dbname = 'worga';
        $this->dblogin = 'root';
        $this->dbpassword = '';

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

    private function setEnvVarWithDbCredentials()
    {
        //Configure for the remote server within add .env file to retreive the remote dbname, dblogin and dbpassword
        $envFilePath = __DIR__ . '/.env';
        if( file_exists($envFilePath) ){
            $envFileContent = file_get_contents($envFilePath);
            $envFileContent = explode("\n", $envFileContent);
            $this->dbhost = $envFileContent[0];
            $this->dbname = $envFileContent[1];
            $this->dblogin = $envFileContent[2];
            $this->dbpassword = $envFileContent[3];
        }
    }
}