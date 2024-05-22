<?php

namespace Worga\src\Controller;

use Worga\src\Model\UserManager;
use Worga\src\Model\Entity\User;
use Worga\src\Classes\Role;

/**
 * Class UserController
 * It manages operations related to users, including database interactions by using the managers and rendering views.
 */
class UserController extends Controller
{
    /** Property for the UserManager instance */
    private $userManager;

    /** Property for the Role instance */
    private $roleUtil;

    /**
     * Constructor method to initialize properties and call the parent constructor
     *
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        // Create an instance of UserManager
        $this->userManager = new UserManager();

        // Create an instance of Role
        $this->roleUtil = new Role();

        // Call the parent constructor with parameters
        parent::__construct($params );
    }

    /**
     * Default action method to render the user view
     */
    public function defaultAction()
    {
        // Check if the user is logged in with admin rights
        if( isset( $_SESSION['userId'] ) && $_SESSION['userRole'] === 'admin' ){
            // Fetch all users and rights from the UserManager
            $listUsers = $this->userManager->getAllUsers();

            // If data is available, render the user view
            if( $listUsers){
                $data = [
                    'listUsers' => $listUsers
                ];
                $this->render('user', $data);
            }
        } else {
            // If not logged in as admin, render the index view
            $this->render('index');
        }
        
    }

    /**
     * Method to verify if the password meets certain conditions
     *
     * @param string $password The password to verify
     * @param string $passwordConfirm The confirmation password
     *
     * @return array|null A message array if verification fails, null otherwise
     */
    public function verifPassword($password, $passwordConfirm): ?array
    {
        // Password conditions: at least 8 characters, one digit, one letter, one special character
        if (strlen($password) < 8 || 
            !preg_match('/[0-9]/', $password) || 
            !preg_match('/[a-zA-Z]/', $password) || 
            !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $message = [
                'type' => 'warning',
                'message' => 'Le mot de passe ne respecte pas les conditions demandées.'
            ];
            return $message;
        } else if ($password != $passwordConfirm) {
            $message = [
                'type' => 'warning',
                'message' => 'La confirmation ne correspond pas au mot de passe.'
            ];
            return $message;
        } else {
            return null;
        }

    }

    /**
     * Method to hash the password using sodium_crypto_pwhash_str
     *
     * @param string $password The password to hash
     *
     * @return string|null The hashed password or null on failure
     */
    public function hashPassword($password): ?string
    {
        // Hash the password using sodium_crypto_pwhash_str
        $passHash = sodium_crypto_pwhash_str(
            $password,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
        );

        return $passHash;
    }

    /**
     * Action method to list all users with params and JSON response
     */
    public function listUsersAction()
    {
        if( !$this->checkIfIsAdmin() ){
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne permettent pas d\'accéder à cette page.'
                ]
            ];
            echo json_encode($data);
            die;
        }

        $nbUsers = $this->userManager->countAll() ?? 0;

        $searchParams = [
            'search'		=> $this->vars['search'],
			'sort'			=> $this->vars['sort'],
			'order'			=> $this->vars['order'],
			'offset'		=> $this->vars['offset'],
			'limit'			=> $this->vars['limit'],
			'searchable'	=> $this->vars['searchable']
        ];

        $listUsers = $this->userManager->getAllUsersWithParams($searchParams);

        $dataBs = [];

        foreach( $listUsers as $user ) {
            $dataBs[] = [
                'id'        => $user->getId(),
                'login'     => $user->getLogin(),
                'role'      => $user->getRoleInFrench(),
                'createdAt' => $user->getCreatedAt()->format('d/m/Y'),
                'updatedAt' => $user->getUpdatedAt()->format('d/m/Y'),
                'isActive'  => $user->getIsActive()
            ];
        }

        $data = [
            "rows"        => $dataBs,
            "total"       => $nbUsers
        ];
        $jsData = json_encode( $data );
        if ($jsData === false) {
            echo "Erreur d'encodage JSON : " . json_last_error_msg();
            error_log("Erreur d'encodage JSON : " . json_last_error_msg());
        } else {
            echo $jsData;
        }
    }

    /**
     * Action method to set the active status of a user
     */
    public function setIsActiveAction()
    {
        if( !$this->checkIfIsAdmin() ){
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];
            echo json_encode($data);
            die;
        }

        $isActive = $this->vars['isActive'] ?? 0;

        if( isset( $this->vars['id'] ) ) {
            $user = $this->userManager->getUserById( $this->vars['id'] );
            $data = [
                'message'   => 'Utilisateur ' . $user->getLogin() 
            ];
            $data['message'] .= $isActive ? ' activé.' : ' désactivé';
            $data['status'] = $this->userManager->setIsActiveByUserId( $this->vars['id'], $isActive ) 
                                ? 'success' 
                                : 'warning';
        }
        echo json_encode( $data );
    }

    /**
     * Action method to render the form to create a new user
     */
    public function createUserAction()
    {
        if( !$this->checkIfIsAdmin() ){
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];
            $this->render('index', $data);
            die;
        };

        $data = [
            'roles' => $this->roleUtil->getRolesInFrenchWithoutAdmin()
        ];
        $this->render('user', $data);
    }

    /**
     * Action method to validate the creation of a new user
     */
    public function createUserValidAction()
    {
        if( !$this->checkIfIsAdmin() ){
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];
            $this->render('index', $data);
            die;
        };

        $data = [];
        $data['roles'] = $this->roleUtil->getRolesInFrench(); 

        // Retrieve input data from the request
        $login = htmlspecialchars( $this->vars['login'] );
        $role = htmlspecialchars($this->vars['role']);
        $role = $this->roleUtil->getRoleInEnglish( $role );
        $password = htmlspecialchars( $this->vars['password'] );
        $passwordConfirm = htmlspecialchars( $this->vars['passwordConfirm'] );
        $isActive = isset($this->vars['isActive']) && $this->vars['isActive'] === "on" ? true : false;
    

        // Create a new User instance
        $user = new User([]);
        $user->setLogin( $login );
        $user->setRole( $role );
        $user->setIsActive( $isActive );

        // Verify the password and display a message if verification fails
        if($message = $this->verifPassword( $password, $passwordConfirm )){
            $data['message'] = $message;
            $this->render('user', $data);
            die;
        }

        // Hash the password and insert the new user
        if( $passHash = $this->hashPassword($password) ){
            $user->setPassword( $passHash );
            if( $user = $this->userManager->insertUser( $user ) ){
                // Display a success message if the insertion is successful
                $data['message']['type'] = 'success';
                $data['message']['message'] = 'Ajout de l\'utilisateur effectué !';
                $data['selectedUser'] = $user;
            } else {
                // Display a warning message if the insertion fails
                $data['message'] = [
                    'type' => 'warning',
                    'message' => 'Echec lors de l\'ajout !'
                ];
            }
        } else {
            // Display a warning message if password hashing fails
            $data['message'] = [
                'type' => 'warning',
                'message' => 'Echec lors de l\'ajout !'
            ];
        }

        // Render the user view
        $this->render('user', $data);
    }
    
    /**
     * Action method to render the form to update an existing user
     */
    public function updateUserAction()
    {
        if( !$this->checkIfIsAdmin() ){
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];
            $this->render('index', $data);
            die;
        };

        $id = $this->vars['id'];
        $user = $this->userManager->getUserById( $id );
        if( $user ){
            $data = [
                'selectedUser' => $user,
                'roles' => $this->roleUtil->getRolesInFrenchWithoutAdmin()
            ];
            $this->render('user', $data);
        }
    }

    /**
     * Action method to validate the update of an existing user
     */
    public function updateUserValidAction()
    {
        if( !$this->checkIfIsAdmin() ){
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];
            $this->render('index', $data);
            die;
        };
        
        $data = [];
        $data['roles'] = $this->roleUtil->getRolesInFrenchWithoutAdmin();

        // Retrieve input data from the request
        $login = htmlspecialchars( $this->vars['login'] );
        $role = isset($this->vars['role']) ? htmlspecialchars($this->vars['role']) : null;
        $password = htmlspecialchars( $this->vars['password'] );
        $passwordConfirm = htmlspecialchars( $this->vars['passwordConfirm'] );
        $isActive = isset($this->vars['isActive']) && $this->vars['isActive'] === "on" ? true : false;
       
        // Check if 'userId' variable is set
        if( $id = $this->vars['userId']){
            // Attempt to get the user details by ID
            $user = $this->userManager->getUserById( $id );
            $user->setLogin( $login );
            if ($role) {
                $role = $this->roleUtil->getRoleInEnglish( $role );
                $user->setRole( $role );
            } else {
                $role = $user->getRole();
            }
            $user->setIsActive( $isActive );
            $data['selectedUser'] = $user;
        
        }
        

        // Verify the password and display a message if verification fails
        if($message = $this->verifPassword( $password, $passwordConfirm )){
            $data['message'] = $message;
            $this->render('user', $data);
            die;
        }

        // Hash the password and update the user
        if( $passHash = $this->hashPassword($password) ){
            $user->setPassword( $passHash );
            if( $this->userManager->updateUser( $user ) ){
                // Display a success message if the update is successful
                $data['message']['type'] = 'success';
                $data['message']['message'] = 'Modification de l\'utilisateur effectuée !';
            } else {
                // Display a warning message if the update fails
                $data['message'] = [
                    'type' => 'warning',
                    'message' => 'Echec lors de la modification!'
                ];
            }
        } else {
            // Display a warning message if password hashing fails
            $data['message'] = [
                'type' => 'warning',
                'message' => 'Echec lors de la modification!'
            ];
        } 

        // Render the user view
        $this->render('user', $data);
    }

}