<?php

namespace Worga\src\Model;

use Worga\src\Model\Entity\FinancialTransaction;

/**
 * Class FinTransManager
 * Manages operations related to financial transactions, including database interactions.
 */
class FinTransManager extends Manager
{
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
     * Inserts a new financial transaction into the database.
     * 
     * @param FinancialTransaction $finTrans The financial transaction object to insert.
     * @return FinancialTransaction|null The inserted financial transaction object, or null if the insertion failed.
     */
    public function insertFinTrans( FinancialTransaction $finTrans ): ?FinancialTransaction
    {
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
        if( $state ) {
            $id = $this->dbManager->db->lastInsertId();
            $finTrans = $this->getFinTransById( $id );
            return $finTrans;
        } else {
            return null;
        }
    }

    /**
     * Updates an existing financial transaction in the database.
     * 
     * @param FinancialTransaction $finTrans The financial transaction object to update.
     * @return FinancialTransaction|null The updated financial transaction object, or null if the update failed.
     */
    public function updateFinTrans( FinancialTransaction $finTrans ): ?FinancialTransaction
    {
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
        $state = $req->execute([
            'title' => $finTrans->getTitle(),
            'description' => $finTrans->getDescription(),
            'category' => $finTrans->getCategory(),
            'amount_ex_vat' => $finTrans->getAmountExVat(),
            'vat_rate' => $finTrans->getVatRate(),
            'fin_trans_date' => $finTrans->getFinTransDate()->format('Y-m-d'),
            'user_id' => $finTrans->getUser()->getId(),
            'id' => $finTrans->getId()
        ]);
        if( $state ) {
            $finTrans = $this->getFinTransById( $finTrans->getId() );
            return $finTrans;
        } else {
            return null;
        }
    }
}