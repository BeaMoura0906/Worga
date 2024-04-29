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
}