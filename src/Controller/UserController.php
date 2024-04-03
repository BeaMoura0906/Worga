<?php

namespace Worga\src\Controller;

use Worga\src\Model\UserManager;
use Worga\src\Model\Entity\User;

class UserController extends Controller
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
        $this->userManager = new UserManager();

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

    public function listUsersAction()
    {

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
}