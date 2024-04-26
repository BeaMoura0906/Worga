<?php

namespace Worga\src\Controller;

use Worga\src\Model\FinTransManager;
use Worga\src\Model\AccountManager;
use Worga\src\Classes\FinTransCategories;

class FinTransController extends Controller
{
    /** Properties */
    private $finTransManager;
    private $accountManager;
    private $finTransCategories;

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
                    $totalToBeDebited = '- ' . bcadd($totalToBeDebited, $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2) . ' €';
                    break;
                case FinTransCategories::CATEGORY_DEBIT:
                    $totalDebit = '- ' . bcadd($totalDebit, $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2) . ' €';
                    break;
                case FinTransCategories::CATEGORY_CREDIT:
                    $totalCredit = '+ ' . bcadd($totalCredit, $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate()), 2) . ' €';
                    break;
            }
        }

        return [
            'totalToBeDebited' => $totalToBeDebited,
            'totalDebit'       => $totalDebit,
            'totalCredit'      => $totalCredit
        ];
    } 
}