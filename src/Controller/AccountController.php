<?php

namespace Worga\src\Controller;

use Worga\src\Model\Entity\Account;
use Worga\src\Model\Entity\Client;
use Worga\src\Classes\FinTransCategories;
use Worga\src\Model\AccountManager;
use Worga\src\Model\ClientManager;

/**
 * Class AccountController
 * It manages operations related to client accounts, including database interactions by using the managers and rendering views.
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
        if( !$this->checkIfUserIsConnected() ) {
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => "Vous devez vous connecter pour accéder à cette page."
                ]
            ];
            $this->render('login', $data);
            exit();
        }

        if( isset($this->vars['clientId']) ) {
            $this->selectedClient = $this->clientManager->getClientById($this->vars['clientId']);
            if( $this->selectedClient ) {
                $this->clientAccount = $this->accountManager->getAccountByClient($this->selectedClient);
                if( $this->clientAccount ) {
                    $finTransCategories = new FinTransCategories();
                    $finTransCategoriesFr = $finTransCategories->getFinTransCategoriesFr();
                    $data = [
                        'selectedClient' => $this->selectedClient, 
                        'clientAccount' => $this->clientAccount,
                        'finTransCategoriesFr' => $finTransCategoriesFr
                    ];
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

    /**
     * Action method to create a new account for the selected client
     */
    public function createAction()
    {   
        if( !$this->checkIfIsAdminOrEditor() ) {
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];

            if( $this->selectedClient ) {
                $data['selectedClient'] = $this->selectedClient;
                $this->render('account', $data);
                exit();
            } else if ( isset($this->vars['clientId']) ) {
                $data['selectedClient'] = $this->clientManager->getClientById($this->vars['clientId']);
                $this->render('account', $data);
                exit();
            } else {
                $data['listClients'] = true;
                $this->render('client', $data);
                exit();
            }
        } 
        
        if( isset( $this->vars['clientId'] ) ) {
            $this->selectedClient = $this->selectedClient ?? $this->clientManager->getClientById($this->vars['clientId']);
            $newAccount = $this->accountManager->insertNewAccountToClient($this->selectedClient);
            if( $newAccount ) {
                header('Location: ' . $this->pathRoot . 'account/getAccount/clientId/' . $this->selectedClient->getId());
                exit;
            } else {
                $data = [
                    'message' => [
                        'message' => "Une erreur est survenue. Veuillez reéssayer.",
                        'type' => 'warning'
                    ],
                    'selectedClient' => $this->selectedClient
                ];
                $this->render('account', $data);
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