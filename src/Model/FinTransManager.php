<?php

namespace Worga\src\Model;

use Worga\src\Model\AccountManager;
use Worga\src\Model\Entity\Account;
use Worga\src\Model\Entity\FinancialTransaction;
use Worga\src\Classes\FinTransCategories;

/**
 * Class FinTransManager
 * Manages operations related to financial transactions, including database interactions.
 */
class FinTransManager extends Manager
{   
    /** Properties */
    private AccountManager $accountManager;

    /** Constructor */
    public function __construct()
    {
        $this->accountManager = new AccountManager();
        parent::__construct();
    }

    /**
     * Retrieves all financial transactions from the database for a given account ID.
     * 
     * @param int $accountId The ID of the account to retrieve financial transactions for.
     * @return array|null An array of FinancialTransaction objects, or null if there are no financial transactions.
     */
    public function getAllFinTransByAccountId(int $accountId): ?array
    {
        $sql = "SELECT * FROM financial_transactions WHERE account_id = :account_id";
        $req = $this->dbManager->db->prepare($sql);
        $req->execute([
            'account_id' => $accountId
        ]);
        if( $data = $req->fetchAll(\PDO::FETCH_ASSOC) ) {
            $finTrans = [];
            foreach( $data as $finTransData ) {
                $finTrans[] = new FinancialTransaction($finTransData);
            }
            return $finTrans;
        } else {
            return null;
        }
    }

    /**
     * Retrieves all financial transactions from the database for a given account ID with parameters.
     * 
     * @param array $params The parameters to filter the financial transactions by.
     * @return array|null An array of FinancialTransaction objects, or null if there are no financial transactions in the database for this account.
     */
    public function getAllFinTransWithParams ( array $params ): ?array
    {
        $order = !empty( $params['order'] ) ? $params['order'] : 'ASC';
        $sort = !empty( $params['sort'] ) ? $this->convertCamelCaseToSnakeCase($params['sort']) : 'fin_trans_date';
        $strLike = false;
        if( !empty( $params['search'] ) && !empty( $params['searchable'] ) ) {
            foreach( $params['searchable'] as $searchItem ) {
                $searchItem = $this->convertCamelCaseToSnakeCase( $searchItem );
                $search = $params['search'];
                $strLike .= $searchItem . " LIKE '%$search%' OR ";
            }
            $strLike = trim( $strLike, ' OR ' );
        }
        $sql = "SELECT * FROM financial_transactions WHERE account_id = :account_id";
        if( $strLike ) {
            $sql .= " AND $strLike";
        }
        $sql .= " ORDER BY $sort $order";

        

        $req = $this->dbManager->db->prepare( $sql );
		if( $req->execute( [ 'account_id' => $params['accountId'] ] ) ) {
            $data = $req->fetchAll( \PDO::FETCH_ASSOC );
            $listFinTrans= [];

            foreach( $data as $finTransData ) {
                $finTrans = new FinancialTransaction($finTransData);
                $listFinTrans[] = $finTrans;
            }

            return $listFinTrans;
        } else {
            return null;
        }
    }

    /**
     * Retrieves a financial transaction from the database by its ID.
     * 
     * @param int $id The ID of the financial transaction to retrieve.
     * @return FinancialTransaction|null The retrieved financial transaction object, or null if the financial transaction does not exist.
     */
    public function getFinTransById( $id ): ?FinancialTransaction
    {
        $sql = "SELECT * FROM financial_transactions WHERE id = :id";
        $req = $this->dbManager->db->prepare($sql);
        $req->execute([
            'id' => $id
        ]);
        if( $data = $req->fetch(\PDO::FETCH_ASSOC) ) {
            $finTrans = new FinancialTransaction($data);
            return $finTrans;
        } else {
            return null;
        }
    }

    /**
     * Calculates the total amount of each category for a given account ID and the rest to invoice and to cash. 
     * 
     * @param Account $account The account to calculate the totals and rests for.
     * @return Account The account with the totals and rests calculated.
     */
    public function calculateTotalsByCategory(Account $account ): Account
    {
        $listFinTrans = $this->getAllFinTransByAccountId( $account->getId() );
        $estimatesTotal = '';
        $invoicesTotal = '';
        $receiptsTotal = '';

        foreach( $listFinTrans as $finTrans ) {
            $amoutIncVat = $finTrans->getAmountIncVat($finTrans->getAmountExVat(), $finTrans->getVatRate());
            switch( $finTrans->getCategory() ) {
                case FinTransCategories::CATEGORY_TO_BE_DEBITED:
                    $estimatesTotal = bcadd( $estimatesTotal, $amoutIncVat, 2 );
                    break;
                case FinTransCategories::CATEGORY_DEBIT:
                    $invoicesTotal = bcadd( $invoicesTotal, $amoutIncVat, 2 );
                    break;
                case FinTransCategories::CATEGORY_CREDIT:
                    $receiptsTotal = bcadd( $receiptsTotal, $amoutIncVat, 2 );
                    break;
            }
        }

        $restToInvoice = bcsub( $estimatesTotal, $invoicesTotal, 2 );
        $restToCash = bcsub( $invoicesTotal, $receiptsTotal, 2 );

        $account->setEstimatesTotal( $estimatesTotal );
        $account->setInvoicesTotal( $invoicesTotal );
        $account->setReceiptsTotal( $receiptsTotal );
        $account->setRestToInvoice( $restToInvoice );
        $account->setRestToCash( $restToCash );

        return $account;
    }

    /**
     * Inserts a new financial transaction into the database with a db transaction which updates the client account.
     * 
     * @param FinancialTransaction $finTrans The financial transaction object to insert.
     * @return FinancialTransaction|null The inserted financial transaction object, or null if the insertion failed.
     * @throws Exception If the db transaction fails because of the financial transaction insertion or the client account update.
     */
    public function insertFinTrans( FinancialTransaction $finTrans ): ?FinancialTransaction
    {
        $this->dbManager->db->beginTransaction();

        try {
            $sql = "INSERT INTO financial_transactions (
                        title,
                        description,
                        category,
                        amount_ex_vat,
                        vat_rate,
                        fin_trans_date,
                        inserted_at,
                        updated_at,
                        account_id,
                        user_id
                    ) VALUES (
                        :title,
                        :description,
                        :category,
                        :amount_ex_vat,
                        :vat_rate,
                        :fin_trans_date,
                        NOW(),
                        NOW(),
                        :account_id,
                        :user_id
                    )";
            $req = $this->dbManager->db->prepare($sql);
            $state = $req->execute([
                'title' => $finTrans->getTitle(),
                'description' => $finTrans->getDescription(),
                'category' => $finTrans->getCategory(),
                'amount_ex_vat' => $finTrans->getAmountExVat(),
                'vat_rate' => $finTrans->getVatRate(),
                'fin_trans_date' => $finTrans->getFinTransDate()->format('Y-m-d'),
                'account_id' => $finTrans->getAccount()->getId(),
                'user_id' => $finTrans->getUser()->getId()
            ]);

            if( !$state ) {
                throw new \Exception('Failed to insert financial transaction.');
            }

            $transId = $this->dbManager->db->lastInsertId();

            $finTrans->setAccount( $this->calculateTotalsByCategory( $finTrans->getAccount() ) );
            $this->accountManager->updateAccount( $finTrans->getAccount() );

            $this->dbManager->db->commit();

            $finTrans = $this->getFinTransById( $transId );
            return $finTrans;
        } catch( \Exception $e ) {
            $this->dbManager->db->rollBack();
            error_log("DB Transaction failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Updates an existing financial transaction in the database with a db transaction which updates the client account.
     * 
     * @param FinancialTransaction $finTrans The financial transaction object to update.
     * @return FinancialTransaction|null The updated financial transaction object, or null if the update failed.
     * @throws Exception If the db transaction fails because of the financial transaction update or the client account update.
     */
    public function updateFinTrans( FinancialTransaction $finTrans ): ?FinancialTransaction
    {
        $this->dbManager->db->beginTransaction();

        try {
            $sql = "UPDATE financial_transactions SET
                        title = :title,
                        description = :description,
                        category = :category,
                        amount_ex_vat = :amount_ex_vat,
                        vat_rate = :vat_rate,
                        fin_trans_date = :fin_trans_date,
                        updated_at = NOW(),
                        user_id = :user_id
                    WHERE id = :id";
            $req = $this->dbManager->db->prepare($sql);
            $req->execute([
                'title' => $finTrans->getTitle(),
                'description' => $finTrans->getDescription(),
                'category' => $finTrans->getCategory(),
                'amount_ex_vat' => $finTrans->getAmountExVat(),
                'vat_rate' => $finTrans->getVatRate(),
                'fin_trans_date' => $finTrans->getFinTransDate()->format('Y-m-d'),
                'user_id' => $finTrans->getUser()->getId(),
                'id' => $finTrans->getId()
            ]);

            if( $req->rowCount() == 0 ) {
                throw new \Exception('No changes were updated to the financial transaction.');
            } 

            $finTrans->setAccount( $this->calculateTotalsByCategory( $finTrans->getAccount() ) );
            $this->accountManager->updateAccount( $finTrans->getAccount() );

            $this->dbManager->db->commit();

            $finTrans = $this->getFinTransById( $finTrans->getId() );
            return $finTrans;
        } catch( \Exception $e ) {
            $this->dbManager->db->rollBack();
            error_log("DB Transaction failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Deletes a financial transaction from the database by its ID with a db transaction which updates the client account.
     * 
     * @param $id The ID of the financial transaction to delete.
     * @return bool True if the financial transaction was deleted successfully, false otherwise.
     * @throws Exception If the db transaction fails because of the financial transaction deletion or the client account update.
     */
    public function deleteFinTransById( $id ): bool
    {   
        $this->dbManager->db->beginTransaction();

        $finTrans = $this->getFinTransById( $id );
        $account = $finTrans->getAccount();

        try {
            $sql = "DELETE FROM financial_transactions WHERE id = :id";
            $req = $this->dbManager->db->prepare($sql);
            $req->execute([
                'id' => $id
            ]);

            if( $req->rowCount() == 0 ) {
                throw new \Exception('No rows were deleted from the financial transactions table.');
            }

            $account = $this->calculateTotalsByCategory( $account );
            $this->accountManager->updateAccount( $account );

            $this->dbManager->db->commit();

            return true;
        } catch( \Exception $e ) {
            $this->dbManager->db->rollBack();
            error_log("DB Transaction failed: " . $e->getMessage());
            return false;
        }
        
    }
}