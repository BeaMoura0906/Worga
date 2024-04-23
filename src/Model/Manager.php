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

        //Configure for the BSC's remote server o2switch 
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
}