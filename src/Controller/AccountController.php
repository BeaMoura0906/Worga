<?php

namespace Worga\src\Controller;

use Worga\src\Model\Entity\Account;
use Worga\src\Model\Entity\Client;
use Worga\src\Model\AccountManager;
use Worga\src\Model\ClientManager;

/**
 * Class AccountController
 * 
 */
class AccountController extends Controller
{
    /** Properties */
    private $accountManager;
    private $clientManager;
    private $selectedClient;
    private $clientAccount;

    /**
     * Constructor method to initialize properties and call the parent constructor
     * 
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params = [])
    {
        $this->accountManager = new AccountManager();
        $this->clientManager = new ClientManager();

        parent::__construct($params);
    }

    /**
     * Default action method to render the account view. It calls getAccountAction() method.
     */
    public function defaultAction()
    {
        $this->getAccountAction();
    }

    /**
     * Action method to render the account view with the client and its account details. If no client is selected, it redirects to the list of clients view. If the selected client has no account, it redirects to the account view without the account details to add one.
     */
    public function getAccountAction()
    {
        if( isset($this->vars['clientId']) ) {
            $this->selectedClient = $this->clientManager->getClientById($this->vars['clientId']);
            if( $this->selectedClient ) {
                $this->clientAccount = $this->accountManager->getAccountByClient($this->selectedClient);
                if( $this->clientAccount ) {
                    $data = ['selectedClient' => $this->selectedClient, 'clientAccount' => $this->clientAccount];
                    $this->render('account', $data);
                } else {
                    $data = ['selectedClient' => $this->selectedClient];
                    $this->render('account', $data);
                }
            } else {
                $data = [
                    'message' => [
                        'message' => "Une erreur est survenue. Veuillez réssayer.",
                        'type' => 'warning'
                    ],
                    'listClients' => true
                ];
                $this->render('client', $data);
            }
        } else {
            $data = [
                'message' => [
                    'message' => "Une erreur est survenue. Veuillez reéssayer.",
                    'type' => 'warning'
                ],
                'listClients' => true
            ];
            $this->render('client', $data);
        }
    }
}