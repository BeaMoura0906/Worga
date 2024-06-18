<?php

namespace Worga\src\Model;

use Worga\src\Model\Entity\Account;
use Worga\src\Model\Entity\Client;

/**
 * Class AccountManager
 * Manages operations related to accounts, including database interactions.
 */
class AccountManager extends Manager
{
    public function getAllAccounts() : ?array 
    {
        $sql = "SELECT * FROM accounts";
        $req = $this->dbManager->db->prepare($sql);
        $req->execute();
        if( $data = $req->fetchAll(\PDO::FETCH_ASSOC) ) {
            $listAccounts = [];
            foreach( $data as $accountData ) {
                $listAccounts[] = new Account($accountData);
            }
            return $listAccounts;
        } else {
            return null;
        }
    }

    /**
     * Retrieves all accounts fromt the database with params.
     * 
     * @param array @params The parameters to filter the accounts by.
     * @return array|null An array of Account objects, or null if there are no account in the database.
     */
    public function getAllAccountsWithParams(array $params): ?array
    {
        $order = !empty( $params['order'] ) ? $params['order'] : 'ASC';
        $sort = !empty( $params['sort'] ) ? $this->convertCamelCaseToSnakeCase($params['sort']) : 'id';
        $strLike = false;
        if( !empty( $params['search'] ) && !empty( $params['searchable'] ) ) {
            foreach( $params['searchable'] as $searchItem ) {
                $searchItem = $this->convertCamelCaseToSnakeCase( $searchItem );
                $search = $params['search'];
                $strLike .= $searchItem . " LIKE '%$search%' OR ";
            }
            $strLike = trim( $strLike, ' OR ' );
        }
        $sql = "SELECT * FROM accounts";
        if( $strLike ) {
            $sql .= " WHERE $strLike";
        }
        $offset = !empty( $params['offset']) ? $params['offset'] : 0;
        $limit = !empty( $params['limit'] ) ? $params['limit'] : 1000;
        $sql .= " ORDER BY $sort $order";
        $sql .= " LIMIT $offset, $limit";

        $req = $this->dbManager->db->prepare( $sql );
		if( $req->execute() ) {
            $data = $req->fetchAll( \PDO::FETCH_ASSOC );
            $listAccounts = [];

            foreach( $data as $accountData ) {
                $account = new Account($accountData);
                $listAccounts[] = $account;
            }

            return $listAccounts;
        } else {
            return null;
        }
    }

    /**
     * Retrieves an account from the database by its ID.
     * 
     * @param int $id The ID of the account to retrieve.
     * @return Account|null The retrieved account object, or null if the account does not exist.
     */
    public function getAccountById($id): ?Account
    {
        $sql = "SELECT * FROM accounts WHERE id = :id";
        $req = $this->dbManager->db->prepare($sql);
        $req->execute(['id' => $id]);
        $data = $req->fetch();
        if($data){
            $account = new Account($data);
            return $account;
        } else {
            return null;
        }
    }
    
    /**
     * Inserts a new account for a client in the database.
     * 
     * @param Client $client The client to create the account for.
     * @return Account|null The newly created account, or null if the account could not be inserted.
     */
    public function insertNewAccountToClient(Client $client): ?Account
    {
        $sql = "INSERT INTO accounts (estimates_total, invoices_total, receipts_total, rest_to_invoice, rest_to_cash, client_id) VALUES (0, 0, 0, 0, 0, :client_id)";
        $req = $this->dbManager->db->prepare($sql);
        $state =$req->execute(['client_id' => $client->getId()]);
        if( $state ){
            $id = $this->dbManager->db->lastInsertId();
            $account = new Account([]);
            $account->setId($id);
            $account->setClient($client);
            return $account;
        } else {
            return null;
        }
    }

    /**
     * Retrieves an account from the database by client.
     * 
     * @param Client $client The client to retrieve the account for.
     * @return Account|null The retrieved account, or null if the account could not be found.
     */
    public function getAccountByClient(?Client $client): ?Account
    {
        if ($client === null) {
            return null;
        }
        
        $sql = "SELECT * FROM accounts WHERE client_id = :client_id";
        $req = $this->dbManager->db->prepare($sql);
        $req->execute(['client_id' => $client->getId()]);
        $data = $req->fetch();
        if ($data) {
            $account = new Account($data);
            $account->setClient($client);
            return $account;
        } else {
            return null;
        }
    }

    /**
     * Updates an account in the database.
     * 
     * @param Account $account The account to update.
     * @return bool True if the account was updated successfully, false otherwise.
     * @throws \Exception If the account could not be updated.
     */
    public function updateAccount(Account $account): bool
    {
        $sql = "UPDATE accounts SET 
                    estimates_total = :estimates_total,
                    invoices_total = :invoices_total,
                    receipts_total = :receipts_total,
                    rest_to_invoice = :rest_to_invoice,
                    rest_to_cash = :rest_to_cash
                WHERE id = :id";
        $req = $this->dbManager->db->prepare($sql);
        $state = $req->execute([
            'estimates_total' => $account->getEstimatesTotal(),
            'invoices_total' => $account->getInvoicesTotal(),
            'receipts_total' => $account->getReceiptsTotal(),
            'rest_to_invoice' => $account->getRestToInvoice(),
            'rest_to_cash' => $account->getRestToCash(),
            'id' => $account->getId()
        ]);

        if(!$state){
            throw new \Exception('Failed to update account');
            return false;
        } else {
            return true;
        }
    }
}