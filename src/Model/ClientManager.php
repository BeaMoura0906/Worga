<?php 

namespace Worga\src\Model;

use Worga\src\Model\Entity\Client;
use Worga\src\Model\Entity\User;

/**
 * Class ClientManager
 * Manages operations related to clients, including database interactions.
 */
class ClientManager extends Manager
{

    /**
     * Inserts a new client into the database.
     * 
     * @param Client $client The client to insert.
     * @return bool True if the client was inserted successfully, false otherwise.
     */
    public function insertClient(Client $client): ?Client
    {
        $sql = "INSERT INTO clients (
                name,
                address,
                phone,
                email,
                other,
                inserted_at,
                updated_at,
                user_id
        ) VALUES (
                :name,
                :address,
                :phone,
                :email,
                :other,
                NOW(),
                NOW(),
                :user_id
        )";

        $req = $this->dbManager->db->prepare($sql);
        $state = $req->execute([
            'name' => $client->getName(),
            'address' => $client->getAddress(),
            'phone' => $client->getPhone(),
            'email' => $client->getEmail(),
            'other' => $client->getOther() ?? null,
            'user_id' => $client->getUser()->getId()
        ]);

        if ($state) {
            $client->setId($this->dbManager->db->lastInsertId());
            return $client;
        } else {
            return null;
        }
    }
}