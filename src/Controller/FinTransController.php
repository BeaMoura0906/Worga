<?php

namespace Worga\src\Controller;

use Worga\src\Model\FinTransManager;

class FinTransController extends Controller
{
    /** Properties */
    private $finTransManager;

    /**
     * FinTransController constructor to initialize properties and call the parent constructor
     * 
     * @param array $params Parameters for the controller
     */
    public function __construct(array $params=[])
    {
        $this->finTransManager = new FinTransManager();
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

        $searchParams['offset'] = '';
        $searchParams['limit'] = '';
        $nbFinTrans = count($this->finTransManager->getAllFinTransWithParams($searchParams));

        $dataBs = [];

        foreach( $listFinTrans as $finTrans ) {
            $dataBs[] = [
                'finTransDate'           => $finTrans->getFinTransDate()->format('d/m/Y'),
                'title'                  => $finTrans->getTitle(),
                $finTrans->getCategory() => $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate())
            ];
        }

        $data = [
            "rows"        => $dataBs,
            "total"       => $nbFinTrans
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