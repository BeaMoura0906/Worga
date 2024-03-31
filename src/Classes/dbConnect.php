<?php

namespace Worga\src\Classes;

class dbConnect
{
    // Public property to hold the PDO instance
    public $db;

    // Private static property to hold the instance of the class (singleton pattern)
    private static $instance = null;

    /**
     * Constructor method to establish a database connection
     *
     * @param string $dsn Database connection string
     * @param string $dblogin Database username
     * @param string $dbpassword Database password
     */
    public function __construct($dsn, $dblogin, $dbpassword){

        // Establish a connection to the database
        try
        {
            // Create a PDO instance and set error mode to display warnings in development mode
            $this->db = new \PDO($dsn, $dblogin, $dbpassword);
            // Only on dev mode
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
        }
        catch(\Exception $e)
        {
            // If an error occurs during the connection, display an error message and terminate
            die('Erreur : '.$e->getMessage());
        }

        
    }

    /**
     * Static method to get the database connection instance (singleton pattern)
     *
     * @param string $dsn Database connection string
     * @param string $dblogin Database username
     * @param string $dbpassword Database password
     * 
     * @return self Returns an instance of the dbConnect class
     */
    public static function getDb($dsn, $dblogin, $dbpassword): self
    {
        // Check if the instance is null (not created yet)
        if( is_null(self::$instance)){
            // Create a new instance of the dbConnect class
            self::$instance = new dbConnect($dsn, $dblogin, $dbpassword);
        }

        // Return the instance
        return self::$instance;
    }

}