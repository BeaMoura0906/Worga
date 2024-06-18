<?php

namespace Worga\src\Controller;

use Worga\src\Model\AccountManager;

/**
 * Class IndexController
 * It manages operations related to the index view, including rendering it. 
 */
class IndexController extends Controller
{
    private AccountManager $accountManager;

    /**
     * Constructor method to initialize properties and call the parent constructor
     *
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        
        $this->accountManager = new AccountManager();

        // Call the parent constructor with parameters
        parent::__construct($params);

    }

    /**
     * Default action method to render the index view
     */
    public function defaultAction()
    {
        if( !$this->checkIfIsAdminOrEditor() ) {
            $this->render('index', []);
        } else {
            $data = [
                'listAccounts' => true
            ];
            $this->render( 'index', $data);
        }
    }

    /**
     * Method action to list all clients' accounts with JSON response.
     */
    public function listAccountsAction()
    {
        if( !$this->checkIfIsAdminOrEditor() ) {
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => "Vous devez vous connecter pour accéder à cette page."
                ]
            ];
            echo json_encode($data);
            exit();
        }

        $searchParams = [
            'search'		=> $this->vars['search'],
			'sort'			=> $this->vars['sort'],
			'order'			=> $this->vars['order'],
			'offset'		=> $this->vars['offset'],
			'limit'			=> $this->vars['limit']
        ];

        $listAccounts = $this->accountManager->getAllAccountsWithParams($searchParams);

        $searchParams['offset'] = '';
        $searchParams['limit'] = '';

        $nbAccounts = count($this->accountManager->getAllAccountsWithParams($searchParams));

        $totalRestToCash = '';
        foreach( $listAccounts as $account ) {
            $totalRestToCash = bcadd($totalRestToCash, $account->getRestToCash(), 2);
        }
        $totals = [
            'totalRestToCash' => number_format($totalRestToCash, 2, ',', ' ') . ' €'
        ];

        $dataBs = [];

        foreach( $listAccounts as $account ) {
            $dataBs[] = [
                'id'                     => $account->getId(),
                'client'                 => $account->getClient()->getLastName() . ' ' . $account->getClient()->getFirstName(),
                'restToCash'             => number_format($account->getRestToCash(), 2, ',', ' ') . ' €'
            ];
        }

        $data = [
            "rows"        => $dataBs,
            "total"       => $nbAccounts,
            "totals"      => $totals
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