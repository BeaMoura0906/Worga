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
        if( $sort === 'lastName' || $sort === 'firstName' ) {
            $sort = $this->convertCamelCaseToSnakeCase( $sort );
        }
        $strLike = false;
        if( !empty( $params['search'] ) && !empty( $params['searchable'] ) ) {
            foreach( $params['searchable'] as $searchItem ) {
                if( $searchItem === 'lastName' || $searchItem === 'firstName' ) {
                    $searchItem = $this->convertCamelCaseToSnakeCase( $searchItem );
                } 
                $search = $params['search'];
                $strLike .= $searchItem . " LIKE '%$search%' OR ";
            }
            $strLike = trim( $strLike, ' OR ' );
        }
        $sql = "SELECT * FROM clients";
        if( $strLike ) {
            $sql .= " WHERE $strLike";
        }
        $offset = !empty( $params['offset']) ? $params['offset'] : 0;
        $limit = !empty( $params['limit'] ) ? $params['limit'] : 1000;
        $sql .= " ORDER BY $sort $order";
        $sql .= " LIMIT $offset, $limit";

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
     * Retrieves a client from the database by its ID.
     * 
     * @param int $clientId The ID of the client to retrieve.
     * @return Client|null The retrieved client object, or null if the client does not exist.
     */
    public function getClientById(int $clientId): ?Client
    {
        $sql = "SELECT * FROM clients WHERE id = :id";
        $req = $this->dbManager->db->prepare( $sql );
        $req->execute([
            'id' => $clientId
        ]);
        if ($clientData = $req->fetch()) {
            $client = new Client($clientData);
            return $client;
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

    /**
     * Updates a client in the database.
     * 
     * @param Client $client The client to update.
     * @return bool True if the client was updated successfully, false otherwise.
     */
    public function updateClient(Client $client): bool
    {
        $sql = "UPDATE clients SET
                    last_name = :last_name,
                    first_name = :first_name,
                    address = :address,
                    phone = :phone,
                    email = :email,
                    other = :other,
                    updated_at = NOW(),
                    user_id = :user_id
                WHERE id = :id";
        $req = $this->dbManager->db->prepare($sql);
        $state = $req->execute([
            'last_name' => $client->getLastName(),
            'first_name' => $client->getFirstName(),
            'address' => $client->getAddress(),
            'phone' => $client->getPhone(),
            'email' => $client->getEmail(),
            'other' => $client->getOther() ?? null,
            'user_id' => $client->getUser()->getId(),
            'id' => $client->getId()
        ]);
        return $state;
    }

    /**
     * Deletes a client from the database.
     */
    public function deleteClientById(int $clientId): bool
    {
        $sql = "DELETE FROM clients WHERE id = :id";
        $req = $this->dbManager->db->prepare($sql);
        $state = $req->execute([
            'id' => $clientId
        ]);
        return $state;
    }
}