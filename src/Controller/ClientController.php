<?php

namespace Worga\src\Controller;

use Worga\src\Model\Entity\Client;
use Worga\src\Model\Entity\User;
use Worga\src\Model\ClientManager;

class ClientController extends Controller
{
    // Property for the ClientManager instance
    private ClientManager $clientManager;

    /**
     * Constructor method to initialize properties and call the parent constructor
     *
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        // Create an instance of ClientManager
        $this->clientManager = new ClientManager();

        // Call the parent constructor with parameters
        parent::__construct($params );
    }

    /**
     * Default action method to render the client view
     */
    public function defaultAction()
    {
        $data= [];
        $this->render('client', $data);
    }
}