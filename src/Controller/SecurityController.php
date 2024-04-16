<?php

namespace Worga\src\Controller;

use Worga\src\Model\UserManager;

class SecurityController extends Controller
{
    // Property for the UserManager instance
    private $userManager;

    /**
     * Constructor method to initialize properties and call the parent constructor
     *
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        // Create an instance of UserManager
        $this->userManager = new userManager();
        // Call the parent constructor with parameters
        parent::__construct($params);
    }

    /**
     * Default action method, redirects to login action
     */
    public function defaultAction()
    {
        $this->loginAction();       
        
    }

    /**
     * Action method to render the login view
     */
    public function loginAction()       
    {
        $data = [];
        // Render the security view
        $this->render('security', $data);
    }

    /**
     * Action method to verify user login credentials
     */
    public function verifyLoginAction()
    {
        $data=[];

        // Check if 'login' and 'password' variables are set
        if( isset( $this->vars['login'] ) && isset( $this->vars['password'] ) ) {
 
            // Attempt to get the user by login
            if( $user = $this->userManager->getUserByLogin( $this->vars['login'] ) ) {
                
                // Verify if user is active first
                if( $user->getIsActive() ) {

                    // Verify the hashed password using sodium_crypto_pwhash_str_verify
                    if( sodium_crypto_pwhash_str_verify( $user->getPassword('password'), $this->vars['password']) ) {
            
                        // Set session variables for user information
                        $_SESSION['userId'] = $user->getId();
                        $_SESSION['userLogin'] = $user->getLogin();
                        $_SESSION['userRole'] = $user->getRole();

                        $data = [
                            'user' => $user
                        ]; 

                        // Redirect to the root path
                        header('Location:' . $this->pathRoot);
                        exit;
                    } else {
                        $_SESSION['login'] = $user->getLogin();
                        // Display a warning message for incorrect password
                        $data['message'] = [
                            'type'  => 'warning',
                            'message'  => 'Le mot de passe est incorrect'
                        ];
                    }
                } else {
                    // Display a warning message for inactive user
                    $data['message'] = [
                        'type'  => 'warning',
                        'message'  => 'Ce compte utilisateur est inactif. Veuillez vous référer à l\'administrateur.'
                    ];
                }
            } else {
                // Display a warning message for incorrect login
                $data['message'] = [
                    'type'  => 'warning',
                    'message'  => 'Le login est incorrect'
                ];
            }
        } 

        // Render the security view with data
        $this->render('security', $data);

    }

    public function logoutAction()
    {
        // Destroy the session
        session_destroy();
        // Redirect to the root path
        header('Location:' . $this->pathRoot);
        exit;
    }

}