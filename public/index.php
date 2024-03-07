<?php
// Display errors only on dev mode
ini_set('display_errors', 1);

// Define the root directory
define('ROOT', dirname(__DIR__));

// Include autoloader files
require_once ROOT . '/autoload.php';
require_once ROOT . '/vendor/autoload.php';

// Start the PHP session
session_start();       

// Decode JSON data from the request, if available
$data = json_decode( file_get_contents( 'php://input' ), true );
$isFetched = false;

// Check if data was fetched from JSON
if( $data !== null ){
    $requestParams = $data;
    $isFetched = true;
} else {
    // If JSON data is not available, use POST data
    $requestParams = $_POST;
}

// Retrieve the query string from the server
$queryString = rtrim($_SERVER['QUERY_STRING'], '/');

// Initialize parameters
$params = [
    'action' => '',
    'vars'  => '',
    'request' => $requestParams,
    'isFetched' => $isFetched
];

// Process the query string
$tabRequest = [];
$controllerName = 'Index';

// Check if the query string is not empty
if ( !empty( $queryString )) {
    // Split the query string into an array
    $tabRequest = explode('/', $queryString);

    // Get the number of elements in the array
    $nbRequest = count( $tabRequest );

    // Get the controller name from the beginning of the array
    $controllerName = ucfirst( array_shift( $tabRequest ) );
}

// Get the action from the array
$params['action'] = array_shift($tabRequest);

// Get the variables from the array
$params['vars'] = isset($tabRequest[0]) ? $tabRequest : '';

// Define the application name
$appName = 'Worga';

// Construct the file name for the controller
$fileName = ROOT . '/src/Controller/' . $controllerName . 'Controller.php';

// Check if the controller file exists
if ( file_exists( $fileName )) {
    // Construct the fully qualified class name for the controller
    $controllerClassName = $appName . '\\src\\Controller\\' . $controllerName . 'Controller';

    // Check if the controller class exists
    if( class_exists( $controllerClassName ) ){
        // Create an instance of the controller
        $controller = new  $controllerClassName( $params );
    } else {
        die('La classe ' . $controllerClassName . ' n\'existe pas.');
    } 
    
} else {
    die('Le fichier n\'existe pas.');
}