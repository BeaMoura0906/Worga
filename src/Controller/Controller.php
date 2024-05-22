<?php

namespace Worga\src\Controller;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

/**
 * Class Controller
 * It is the base class for all controllers in the application.
 */
abstract class Controller
{
    /** Properties for Twig, view paths, controller, root path, action, variables, and fetch status */ 
    protected $twig; 
    protected $pathView = 'View';
    protected $controller;
    protected $pathRoot;
    protected $action;
    protected $vars = [];
    protected $isFetched = false;

    /**
     * Constructor method to set up the controller and execute the appropriate action
     *
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        // Set parameters and paths
        $this->setParams( $params );
        $this->pathView = dirname(__DIR__) . DIRECTORY_SEPARATOR . $this->pathView;
        $this->pathRoot = str_replace( $_SERVER['QUERY_STRING'], '', $_SERVER['REDIRECT_URL'] );

        // Set up Twig environment for rendering views
        $loader = new FilesystemLoader( $this->pathView );
        // Only on dev mode
        $this->twig = new Environment( $loader, ['debug' => false]);
        $this->twig->addGlobal( 'pathRoot', $this->pathRoot );
        $this->twig->addGlobal( 'session', $_SESSION );
        $this->twig->addExtension(new DebugExtension());
        
        // Execute the specified action or the default action
        if( $this->action ){            
            $action = $this->action . 'Action';            
            $this->$action();            
        } else {
            $this->defaultAction();
        }

    }

    /**
     * Abstract method for the default action that must be implemented by subclasses
     */
    abstract public function defaultAction();

    /**
     * Method to set parameters for the controller
     *
     * @param array $params Parameters for the controller
     */
    protected function setParams(array $params=[])
    {
        // Set action, variables, request parameters, and fetch status
        if( !empty( $params['action'])){
            $this->action = $params['action'];
        }
        if( !empty( $params['vars']) && is_array($params['vars'])){
            $nbParams = count( $params['vars'] );
            if( $nbParams > 1 ){
                $i = 0;
                while( $i < $nbParams){
                    $this->vars[$params['vars'][$i]] = isset($params['vars'][$i+1]) ? $params['vars'][$i+1] : '';
                    $i += 2;
                }
            }
            if( !empty( $params['redirect'] ) ) {
                $params['request'] = unserialize( base64_decode( $params['redirect'] ) );
            }
        }
        if( !empty( $params['request'] ) ){
            foreach( $params['request'] as $k=>$v ){
                $this->vars[$k] = $v;
            }
        }
        $this->isFetched = $params['isFetched'];
    }

    /**
     * Method to render a view using Twig
     *
     * @param string $view Name of the view
     * @param array $data Data to be passed to the view
     */
    protected function render($view, $data=[])
    {   
        // Extract data and render the view
        extract( $data );
        $fileNameView = ucfirst( $view ) . 'View.twig';
        $filePath = $this->pathView . '/' . $fileNameView;
        if( file_exists( $filePath ) ) {
            echo $this->twig->render( $fileNameView, $data );
        }  else {
            // Display an error if the view file does not exist
            // Only on dev mode
            die('View file not exists');
        } 
        
    }

    /**
     * Check if the user is connected
     *
     * @return bool True if the user is connected, false otherwise
     */
    protected function checkIfUserIsConnected()
    {
        if( isset( $_SESSION['userId'] ) ) { return true; } else { return false; }
    }

    /**
     * Check if the user is an admin or an editor
     * 
     * @return bool True if the user is an admin or an editor, false otherwise
     */
    protected function checkIfIsAdminOrEditor()
    {
        if( isset( $_SESSION['userId'] ) && $_SESSION['userRole'] == 'admin' || $_SESSION['userRole'] == 'editor' ) { return true; } else { return false; }
    }

    /**
     * Method to check if the user is logged in with admin rights
     * 
     * @return bool True if the user is logged in with admin rights, false otherwise
     */
    protected function checkIfIsAdmin()
    {
        if( isset( $_SESSION['userId'] ) && $_SESSION['userRole'] === 'admin' ){
            return true;
        } else {
            return false;
        }
    }
}
