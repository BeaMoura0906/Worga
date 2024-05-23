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

use FPDF;

/**
 * Class FinTransController
 * It manages operations related to financial transactions, including database interactions by using the managers and rendering views.
 */
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

        $data = [
            'listFinTrans' => true
        ];
        $this->render('account', $data);  
    }

    /**
     * Method action to list all financial transactions for an account with JSON response.
     */
    public function listFinTransAction()
    {
        if( !$this->checkIfUserIsConnected() ) {
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
			'limit'			=> $this->vars['limit'],
			'searchable'	=> $this->vars['searchable'],
            'accountId'     => $this->vars['accountId']
        ];

        $listFinTrans = $this->finTransManager->getAllFinTransWithParams($searchParams);

        $searchParams['offset'] = '';
        $searchParams['limit'] = '';

        $nbFinTrans = count($this->finTransManager->getAllFinTransWithParams($searchParams));

        $listFinTransWithoutParams = $this->finTransManager->getAllFinTransByAccountId($this->vars['accountId']);
        $totals = $this->calculateTotals($listFinTransWithoutParams);
        $totals['totalToBeDebited'] = '- ' . number_format($totals['totalToBeDebited'], 2, ',', ' ') . ' €';
        $totals['totalDebit'] = '- ' . number_format($totals['totalDebit'], 2, ',', ' ') . ' €';
        $totals['totalCredit'] = '+ ' . number_format($totals['totalCredit'], 2, ',', ' ') . ' €';

        $dataBs = [];

        foreach( $listFinTrans as $finTrans ) {
            $amountIncVat = '';
            switch ($finTrans->getCategory()) {
                case FinTransCategories::CATEGORY_TO_BE_DEBITED:
                    $amountIncVat = '- ' . number_format($finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2, ',', ' ') . ' €';
                    break;
                case FinTransCategories::CATEGORY_DEBIT:
                    $amountIncVat = '- ' . number_format($finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2, ',', ' ') . ' €';
                    break;
                case FinTransCategories::CATEGORY_CREDIT:
                    $amountIncVat = '+ ' . number_format($finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2, ',', ' ') . ' €';
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
            'totalToBeDebited' => $totalToBeDebited,
            'totalDebit'       => $totalDebit,
            'totalCredit'      => $totalCredit
        ];
    }

    /**
     * Method action to export the list of financial transactions in PDF format.
     */
    public function exportToPdfAction()
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

        if (isset($this->vars['accountId'])) {
            $accountId = $this->vars['accountId'];
            $account = $this->accountManager->getAccountById($accountId);
            $listFinTrans = $this->finTransManager->getAllFinTransByAccountId($accountId);
            $totals = $this->calculateTotals($listFinTrans);

            // FPDF instance with landscape format
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();

            // Add DejaVu font
            $pdf->AddFont('DejaVu', '', 'DejaVuSans.php');
            $pdf->AddFont('DejaVu-Bold', '', 'DejaVuSans-Bold.php');
            $pdf->SetFont('DejaVu-Bold', '', 14);

            // Function to convert UTF-8 to Windows-1252
            function toWin1252($text) {
                return iconv('UTF-8', 'Windows-1252//IGNORE', $text);
            }

            // Function to truncate the text in function of the max width
            function truncateText($pdf, $text, $maxWidth) {
                while ($pdf->GetStringWidth($text) > $maxWidth) {
                    $text = substr($text, 0, -1);
                }
                return $text;
            }

            // Function to display the header of the table
            function headerTable($pdf, $widths) {
                $header = array(toWin1252('Date'), toWin1252('Titre'), toWin1252('Description'), toWin1252('À débiter'), toWin1252('Débit'), toWin1252('Crédit'));
                foreach ($header as $key => $heading) {
                    $pdf->Cell($widths[$key], 10, $heading, 1);
                }
                $pdf->Ln();
            }

            // PDF Title
            $pdf->Cell(0, 10, toWin1252('Client #' . $account->getClient()->getId() . ' : ' . $account->getClient()->getLastName() . ' ' . $account->getClient()->getFirstName() . ' | Relevé de Compte Client'), 0, 1, 'C');
            $pdf->Ln(10);

            // Table Header
            $pdf->SetFont('DejaVu-Bold', '', 10);
            $widths = array(30, 50, 80, 40, 40, 40);
            headerTable($pdf, $widths);

            // Table Body 
            $pdf->SetFont('DejaVu', '', 10);
            foreach ($listFinTrans as $finTrans) {
                // Check if page needs to be created in function of the remaining space
                if ($pdf->GetY() > 190) {
                    $pdf->AddPage();
                    headerTable($pdf, $widths);
                }

                $toBeDebited = ' ';
                $credit = ' ';
                $debit = ' ';

                switch ($finTrans->getCategory()) {
                    case FinTransCategories::CATEGORY_TO_BE_DEBITED:
                        $toBeDebited = '- ' . toWin1252(number_format($finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2, ',', ' ') . ' EUR');
                        break;
                    case FinTransCategories::CATEGORY_DEBIT:
                        $debit = '- ' . toWin1252(number_format($finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2, ',', ' ') . ' EUR');
                        break;
                    case FinTransCategories::CATEGORY_CREDIT:
                        $credit = '+ ' . toWin1252(number_format($finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2, ',', ' ') . ' EUR');
                        break;
                }

                $date = toWin1252($finTrans->getFinTransDate()->format('d/m/Y'));
                $title = truncateText($pdf, toWin1252($finTrans->getTitle()), $widths[1]);
                $description = truncateText($pdf, toWin1252($finTrans->getDescription()), $widths[2]);
                
                $pdf->Cell($widths[0], 10, $date, 1);
                $pdf->Cell($widths[1], 10, $title, 1);
                $pdf->Cell($widths[2], 10, $description, 1);
                $pdf->Cell($widths[3], 10, $toBeDebited, 1, 0, 'R');
                $pdf->Cell($widths[4], 10, $debit, 1, 0, 'R');
                $pdf->Cell($widths[5], 10, $credit, 1, 0, 'R');
                $pdf->Ln();
            }

            // Table Footer
            $pdf->SetFont('DejaVu-Bold', '', 10);
            $pdf->Cell($widths[0] + $widths[1] + $widths[2], 10, 'Total', 1);
            $pdf->Cell($widths[3], 10, toWin1252('- ' . number_format($totals['totalToBeDebited'], 2, ',', ' ') . ' EUR'), 1, 0, 'R');
            $pdf->Cell($widths[4], 10, toWin1252('- ' . number_format($totals['totalDebit'], 2, ',', ' ') . ' EUR'), 1, 0, 'R');
            $pdf->Cell($widths[5], 10, toWin1252('+ ' . number_format($totals['totalCredit'], 2, ',', ' ') . ' EUR'), 1, 0, 'R');
            $pdf->Ln();

            // PDF Output
            $pdf->Output('D', 'releve_de_compte_client_n' . $account->getId() . '.pdf');

            $data = [
                'selectedClient' => $account->getClient(),
                'clientAccount' => $account,
                'finTransCategoriesFr' => $this->finTransCategories->getFinTransCategoriesFr()
            ];
            $this->render('account', $data);
        } 
    }

    /**
     * Method action to add a new financial transaction.
     */
    public function addFinTransValidAction()
    {
        if( !$this->checkIfIsAdminOrEditor() ) {
            $account = isset($this->vars['accountId']) ? $this->accountManager->getAccountById($this->vars['accountId']) : null;
            $selectedClient = $account !== null ? $account->getClient() : null;

            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne vous permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];

            if( $account !== null && $selectedClient !== null ) {
                $data['selectedClient'] = $selectedClient;
                $data['clientAccount'] = $account;
                $data['finTransCategoriesFr'] = $this->finTransCategories->getFinTransCategoriesFr();
                $this->render('account', $data);
                exit();
            } else {
                $data['listClients'] = true;
                $this->render('clients', $data);
                exit();
            }
        }

        if( isset($this->vars['accountId']) && isset($this->vars['date']) && isset($this->vars['title']) && isset($this->vars['description']) && isset($this->vars['category']) && isset($this->vars['amount']) && isset($this->vars['vatRate']) ) {
            $date = filter_var($this->vars['date']);
            $title = filter_var($this->vars['title']);
            $description = filter_var($this->vars['description']);
            $category = filter_var($this->vars['category']);
            $amount = filter_var($this->vars['amount']);
            $vatRate = filter_var($this->vars['vatRate']);

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
        if( !$this->checkIfIsAdminOrEditor() ) {
            $account = isset($this->vars['accountId']) ? $this->accountManager->getAccountById($this->vars['accountId']) : null;
            $selectedClient = $account !== null ? $account->getClient() : null;

            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne vous permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];

            if( $account !== null && $selectedClient !== null ) {
                $data['selectedClient'] = $selectedClient;
                $data['clientAccount'] = $account;
                $data['finTransCategoriesFr'] = $this->finTransCategories->getFinTransCategoriesFr();
                $this->render('account', $data);
                exit();
            } else {
                $data['listClients'] = true;
                $this->render('clients', $data);
                exit();
            }
        }

        if( isset($this->vars['finTransId']) && isset($this->vars['date']) && isset($this->vars['title']) && isset($this->vars['description']) && isset($this->vars['category']) && isset($this->vars['amount']) && isset($this->vars['vatRate']) ) {
            $finTrans = $this->finTransManager->getFinTransById(filter_var($this->vars['finTransId']));
            
            $date = filter_var($this->vars['date']);
            $title = filter_var($this->vars['title']);
            $description = filter_var($this->vars['description']);
            $category = filter_var($this->vars['category']);
            $amount = filter_var($this->vars['amount']);
            $vatRate = filter_var($this->vars['vatRate']);

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

    /**
     * Method action to delete a financial transaction.
     */
    public function deleteFinTransAction()
    {
        if( !$this->checkIfIsAdminOrEditor() ) {
            $account = isset($this->vars['accountId']) ? $this->accountManager->getAccountById($this->vars['accountId']) : null;
            $selectedClient = $account !== null ? $account->getClient() : null;

            $data = [
                'message' => [
                    'type' => 'warning',
                    'message' => 'Vos droits d\'accès ne vous permettent pas d\'accéder à cette fonctionnalité.'
                ]
            ];

            if( $account !== null && $selectedClient !== null ) {
                $data['selectedClient'] = $selectedClient;
                $data['clientAccount'] = $account;
                $data['finTransCategoriesFr'] = $this->finTransCategories->getFinTransCategoriesFr();
                $this->render('account', $data);
                exit();
            } else {
                $data['listClients'] = true;
                $this->render('clients', $data);
                exit();
            }
        }

        if( isset($this->vars['finTransId']) ) {
            $finTransId = filter_var($this->vars['finTransId']);
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