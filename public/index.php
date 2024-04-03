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
    $requestParams = $_REQUEST;
}

$queryString = rtrim( $_SERVER['QUERY_STRING'], '/' );


$nbRequest = 0;
if( !empty( $queryString ) ) {
    $tabRequest = explode( '/', $queryString );
    $nbRequest = count( $tabRequest );
}
// Split params in two field : action & vars
$params = [
    'action'    =>'', 
    'vars'      =>'',
    'request'   => $requestParams,
    'isFetched' => $isFetched,
    'redirect'  => ''
];

if( $nbRequest >=1 && !empty( $tabRequest[0] ) ) {
	// Retrieve controller name
    $controllerName = ucfirst( array_shift( $tabRequest ) );
    if( isset( $tabRequest[0] ) ) {
		// Retrieve action
        $params['action'] = array_shift( $tabRequest );
    }
    // Retrieve redirect
    if( isset( $_SESSION['redirect'] ) ) {
        $params['redirect'] = $_SESSION['redirect'];
		unset( $_SESSION['redirect'] );
    }
	// Retrieve vars
    $params['vars'] = $tabRequest;
} else {
    $controllerName = 'Index';
}

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