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
     * Retrieves the number of clients in the database.
     * 
     * @return int|null The number of clients in the database, or null if there are no clients in the database.
     */
    public function countAllClients(): ?int
    {
        $sql = "SELECT count(*) FROM clients";
        $req = $this->dbManager->db->query( $sql );
        if ($req->execute()) {
            $nbClients = $req->fetch();
            return $nbClients[0];
        } else {
            return null;
        }
    }

    /**
     * Retrieves all clients from the database.
     * 
     * @return array|null An array of Client objects, or null if there are no clients in the database.
     */
    public function getAllClients(): ?array
    {
        $sql = "SELECT * FROM clients";
        $req = $this->dbManager->db->prepare($sql);
        if ($req->execute()) {
            $clientsData = $req->fetchAll(\PDO::FETCH_ASSOC);
            $clients = [];
            foreach ($clientsData as $clientData) {
                $client = new Client($clientData);
                $clients[] = $client;
            }
            return $clients;
        } else {
            return null;
        }
    }

    /**
     * Retrieves all clients from the database with parameters.
     * 
     * @param array $params The parameters to filter the clients by.
     * @return array|null An array of Client objects, or null if there are no clients in the database.
     */
    public function getAllClientsWithParams ( array $params ): ?array
    {
        $order = !empty( $params['order'] ) ? $params['order'] : 'ASC';
        $sort = !empty( $params['sort'] ) ? $params['sort'] : 'id';
        $strLike = false;
        if( !empty( $params['search'] ) && !empty( $params['searchable'] ) ) {
            foreach( $params['searchable'] as $searchItem ) {
                $search = $params['search'];
                $strLike .= $searchItem . " LIKE '%$search%' OR ";
            }
            $strLike = trim( $strLike, ' OR ' );
        }
        $sql = "SELECT * FROM clients";
        if( $strLike ) {
            $sql .= " WHERE $strLike";
        }
        $sql .= " ORDER BY $sort $order";

        $req = $this->dbManager->db->prepare( $sql );
		if( $req->execute()){
            $clientsData = $req->fetchAll( \PDO::FETCH_ASSOC );
            $clients= [];

            foreach( $clientsData as $clientData ) {
                $client = new Client( $clientData );
                $clients[] = $client;
            }
            
            return $clients;
        } else {
            return null;
        }
    }

    /**
     * Checks if a client with the given last name and first name exists in the database.
     * 
     * @param string $lastName The last name of the client.
     * @param string $firstName The first name of the client.
     * @return bool True if the client exists, false otherwise.
     */
    public function checkIfClientExists(string $lastName, string $firstName): bool
    {
        $sql = "SELECT * FROM clients WHERE last_name = :last_name AND first_name = :first_name";
        $req = $this->dbManager->db->prepare( $sql );
        $req->execute([
            'last_name' => $lastName,
            'first_name' => $firstName
        ]);
        if ($req->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Inserts a new client into the database.
     * 
     * @param Client $client The client to insert.
     * @return bool True if the client was inserted successfully, false otherwise.
     */
    public function insertClient(Client $client): ?Client
    {
        $sql = "INSERT INTO clients (
                last_name,
                first_name,
                address,
                phone,
                email,
                other,
                inserted_at,
                updated_at,
                user_id
        ) VALUES (
                :last_name,
                :first_name,
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
            'last_name' => $client->getLastName(),
            'first_name' => $client->getFirstName(),
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