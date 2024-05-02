<?php

namespace Worga\src\Controller;

use Worga\src\Model\FinTransManager;
use Worga\src\Model\AccountManager;
use Worga\src\Model\DocumentManager;
use Worga\src\Classes\FinTransCategories;
use Worga\src\Model\Entity\FinancialTransaction;
use Worga\src\Model\Entity\Account;
use Worga\src\Model\Entity\User;

use DateTime;

class FinTransController extends Controller
{
    /** Properties */
    private $finTransManager;
    private $accountManager;
    private $finTransCategories;
    private $documentManager;

    /**
     * FinTransController constructor to initialize properties and call the parent constructor
     * 
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        $this->finTransManager = new FinTransManager();
        $this->accountManager = new AccountManager();
        $this->finTransCategories = new FinTransCategories();
        $this->documentManager = new DocumentManager();

        parent::__construct($params);
    }

    /**
     * Default action method to render the account view with the list of financial transactions
     */
    public function defaultAction() 
    {
        $data = [
            'listFinTrans' => true
        ];
        $this->render('account', $data);  
    }

    public function listFinTransAction()
    {
        $searchParams = [
            'search'		=> $this->vars['search'],
			'sort'			=> $this->vars['sort'],
			'order'			=> $this->vars['order'],
			'offset'		=> $this->vars['offset'],
			'limit'			=> $this->vars['limit'],
			'searchable'	=> $this->vars['searchable'],
            'accountId'     => $this->vars['accountId']
        ];

        $listFinTrans = $this->finTransManager->getAllFinTransWithParams($searchParams);

        $searchParams['order'] = '';
        $searchParams['sort'] = '';

        $nbFinTrans = count($this->finTransManager->getAllFinTransWithParams($searchParams));

        $listFinTransWithoutParams = $this->finTransManager->getAllFinTransByAccountId($this->vars['accountId']);
        $totals = $this->calculateTotals($listFinTransWithoutParams);

        $dataBs = [];

        foreach( $listFinTrans as $finTrans ) {
            $amountIncVat = '';
            switch ($finTrans->getCategory()) {
                case FinTransCategories::CATEGORY_TO_BE_DEBITED:
                    $amountIncVat = '- ' . $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()) . ' €';
                    break;
                case FinTransCategories::CATEGORY_DEBIT:
                    $amountIncVat = '- ' . $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()) . ' €';
                    break;
                case FinTransCategories::CATEGORY_CREDIT:
                    $amountIncVat = '+ ' . $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()) . ' €';
                    break;
            }

            $dataBs[] = [
                'id'                     => $finTrans->getId(),
                'finTransDate'           => $finTrans->getFinTransDate()->format('d/m/Y'),
                'title'                  => $finTrans->getTitle(),
                'description'            => $finTrans->getDescription(),
                $finTrans->getCategory() => $amountIncVat
            ];
        }

        $data = [
            "rows"        => $dataBs,
            "total"       => $nbFinTrans,
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

    /**
     * Calculate the totals of the financial transactions in function of the category within bcmath.
     * 
     * @param array $listFinTrans List of financial transactions
     * @return array The list of totals
     */
    private function calculateTotals(array $listFinTrans): array
    {
        $totalToBeDebited = '';
        $totalDebit = '';
        $totalCredit = '';

        foreach ($listFinTrans as $finTrans) {
            switch ($finTrans->getCategory()) {
                case FinTransCategories::CATEGORY_TO_BE_DEBITED:
                    $totalToBeDebited = bcadd($totalToBeDebited, $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2);
                    break;
                case FinTransCategories::CATEGORY_DEBIT:
                    $totalDebit = bcadd($totalDebit, $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2);
                    break;
                case FinTransCategories::CATEGORY_CREDIT:
                    $totalCredit = bcadd($totalCredit, $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2);
                    break;
            }
        }

        return [
            'totalToBeDebited' => '- ' . $totalToBeDebited . ' €',
            'totalDebit'       => '- ' . $totalDebit . ' €',
            'totalCredit'      => '+ ' . $totalCredit . ' €'
        ];
    }

    /**
     * Method action to add a new financial transaction.
     */
    public function addFinTransValidAction()
    {
        if( isset($this->vars['accountId']) && isset($this->vars['date']) && isset($this->vars['title']) && isset($this->vars['description']) && isset($this->vars['category']) && isset($this->vars['amount']) && isset($this->vars['vatRate']) ) {
            $date = htmlentities($this->vars['date']);
            $title = htmlentities($this->vars['title']);
            $description = htmlentities($this->vars['description']);
            $category = htmlentities($this->vars['category']);
            $amount = htmlentities($this->vars['amount']);
            $vatRate = htmlentities($this->vars['vatRate']);

            $date = new DateTime($date);
            $date = $date->format('Y-m-d');

            $category = html_entity_decode($category);
            $category = FinTransCategories::getRoleInEnglish($category);

            $newFinTrans = new FinancialTransaction([
                'finTransDate' => (string) $date,
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'amountExVat' => $amount,
                'vatRate' => $vatRate,
            ]);

            $newFinTrans->setAccount( new Account(['id' => $this->vars['accountId']]) );
            $newFinTrans->setUser( new User(['id' => $_SESSION['userId']]) );
            $newFinTrans = $this->finTransManager->insertFinTrans($newFinTrans);

            if( $newFinTrans !== null ) {
                $clientId = $newFinTrans->getAccount()->getClient()->getId();
                header('Location: ' . $this->pathRoot . 'account/getAccount/clientId/' . $clientId);
                exit;
            } else {
                $account = $this->accountManager->getAccountById($this->vars['accountId']);
                $selectedClient = $account->getClient();
                $data = [
                    'message' => [
                        'type' => 'warning',
                        'message' => 'Une erreur est survenue. Veuillez réessayer.'
                    ],
                    'selectedClient' => $selectedClient,
                    'clientAccount' => $account,
                    'finTransCategoriesFr' => $this->finTransCategories->getFinTransCategoriesFr()
                ];
                $this->render('account', $data);
            }
        } else if ( isset($this->vars['accountId']) ) {
            $account = $this->accountManager->getAccountById($this->vars['accountId']);
            $selectedClient = $account->getClient();
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Une erreur est survenue. Veuillez réessayer.'
                ],
                'selectedClient' => $selectedClient,
                'clientAccount' => $account,
                'finTransCategoriesFr' => $this->finTransCategories->getFinTransCategoriesFr()
            ];
            $this->render('account', $data);
        } else {
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Une erreur est survenue. Veuillez réessayer.'
                ],
                'listClients' => true
            ];
            $this->render('client', $data);
        }
    }

    /**
     * Get the financial transaction to be displayed on modal view with JSON response.
     */
    public function getFinTransAction()
    {
        if( isset($this->vars['finTransId']) ) {
            $finTransId = $this->vars['finTransId'];
            if( $finTrans = $this->finTransManager->getFinTransById($finTransId) ) {
                $category = $this->finTransCategories->getFinTransCategoryFr($finTrans->getCategory());

                $data = [
                    'accountId'              => $finTrans->getAccount()->getId(),
                    'finTransId'             => $finTrans->getId(),
                    'category'               => $category,
                    'date'                   => $finTrans->getFinTransDate()->format('Y-m-d'),
                    'title'                  => $finTrans->getTitle(),
                    'description'            => $finTrans->getDescription(),
                    'amountIncVat'           => $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()),
                    'amountExVat'            => $finTrans->getAmountExVat(),
                    'vatRate'                => $finTrans->getVatRate(),
                    'insertedAt'             => $finTrans->getInsertedAt()->format('d/m/Y à H:i'),
                    'updatedAt'              => $finTrans->getUpdatedAt()->format('d/m/Y à H:i'),
                    'user'                   => $finTrans->getUser()->getLogin()
                ];

                $document = $this->documentManager->getDocumentByFinTransId($finTransId);

                if( $document ) {
                    $data['docPath'] = $this->pathRoot . 'document/viewDocument/docId/' . $document->getId();
                    $data['docName'] = $document->getName();
                }

                $jsData = json_encode( $data );

                if ($jsData === false) {
                    echo "Erreur d'encodage JSON : " . json_last_error_msg();
                    error_log("Erreur d'encodage JSON : " . json_last_error_msg());
                } else {
                    echo $jsData;
                }
            }
        }
    }

    /**
     * Method action to edit a financial transaction.
     */
    public function editFinTransValidAction()
    {
        if( isset($this->vars['finTransId']) && isset($this->vars['date']) && isset($this->vars['title']) && isset($this->vars['description']) && isset($this->vars['category']) && isset($this->vars['amount']) && isset($this->vars['vatRate']) ) {
            $finTrans = $this->finTransManager->getFinTransById(htmlentities($this->vars['finTransId']));
            
            $date = htmlentities($this->vars['date']);
            $title = htmlentities($this->vars['title']);
            $description = htmlentities($this->vars['description']);
            $category = htmlentities($this->vars['category']);
            $amount = htmlentities($this->vars['amount']);
            $vatRate = htmlentities($this->vars['vatRate']);

            $date = new DateTime($date);
            $date = $date->format('Y-m-d');

            $category = html_entity_decode($category);
            $category = FinTransCategories::getRoleInEnglish($category);

            $finTrans->setFinTransDate($date);
            $finTrans->setTitle($title);
            $finTrans->setDescription($description);
            $finTrans->setCategory($category);
            $finTrans->setAmountExVat($amount);
            $finTrans->setVatRate($vatRate);

            $finTrans->setUser( new User(['id' => $_SESSION['userId']]) );

            if( $finTrans = $this->finTransManager->updateFinTrans($finTrans) ) {
                $clientId = $finTrans->getAccount()->getClient()->getId();
                header('Location: ' . $this->pathRoot . 'account/getAccount/clientId/' . $clientId);
                exit;
            } else {
                $account = $this->accountManager->getAccountById($this->vars['accountId']);
                $selectedClient = $account->getClient();
                $data = [
                    'message' => [
                        'type' => 'warning',
                        'message' => 'Une erreur est survenue. Veuillez réessayer.'
                    ],
                    'selectedClient' => $selectedClient,
                    'clientAccount' => $account,
                    'finTransCategoriesFr' => $this->finTransCategories->getFinTransCategoriesFr()
                ];
                $this->render('account', $data);
            }
        } else if ( isset($this->vars['accountId']) ) {
            $account = $this->accountManager->getAccountById($this->vars['accountId']);
            $selectedClient = $account->getClient();
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Une erreur est survenue. Veuillez réessayer.'
                ],
                'selectedClient' => $selectedClient,
                'clientAccount' => $account,
                'finTransCategoriesFr' => $this->finTransCategories->getFinTransCategoriesFr()
            ];
            $this->render('account', $data);
        } else {
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Une erreur est survenue. Veuillez réessayer.'
                ],
                'listClients' => true
            ];
            $this->render('client', $data);
        }
    }

    public function deleteFinTransAction()
    {
        if( isset($this->vars['finTransId']) ) {
            $finTransId = htmlentities($this->vars['finTransId']);
            $finTrans = $this->finTransManager->getFinTransById($finTransId);
            $account = $finTrans->getAccount();
            $client = $account->getClient();
            $clientId = $client->getId();
            if( $this->finTransManager->deleteFinTransById($finTransId) ) {
                header('Location: ' . $this->pathRoot . 'account/getAccount/clientId/' . $clientId);
                exit;
            } else {
                $data = [
                    'message' => [
                        'type' => 'warning',
                        'message' => 'Une erreur est survenue. Veuillez réessayer.'
                    ],
                    'selectedClient' => $client,
                    'clientAccount' => $account,
                    'finTransCategoriesFr' => $this->finTransCategories->getFinTransCategoriesFr()
                ];
                $this->render('account', $data);
            }
        } else {
            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Une erreur est survenue. Veuillez réessayer.'
                ],
                'listClients' => true
            ];
            $this->render('client', $data);
        }
    }
}