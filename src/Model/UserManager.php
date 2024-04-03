<?php

namespace Worga\src\Model;

use Worga\src\Model\Entity\User;

/**
 * Class UserManager
 * Manages operations related to users, including database interactions.
 */
class UserManager extends Manager
{
    /**
     * Get all users.
     *
     * @return array|null List of User objects or null if an error occurs.
     */
    public function getAllUsers(): ?array
    {
        $listUsers = [];
        $sql = "SELECT * FROM users";
        $req = $this->dbManager->db->prepare( $sql );
        if( $req->execute()){
            $listUserData = $req->fetchAll( \PDO::FETCH_ASSOC );

            foreach( $listUserData as $userData){
                $user = new User($userData);
                $listUsers[] = $user;                
            }
            
            return $listUsers;
        } else {
            return null;
        }
    }

    /**
     * Count all users.
     * 
     * @return int Number of users.
     */
    public function countAll(): int
    {
        $sql = "SELECT count(*) FROM users";
        $response = $this->dbManager->db->query( $sql );
        $nbUsers = $response->fetch();
        return $nbUsers[0];
    }


    /**
     * Get all users.
     * 
     * @param array $params Parameters for the query.
     * @return array|null List of User objects or null if an error occurs.
     */
    public function getAllUsersWithParams ( array $params ): ?array
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
        $sql = "SELECT * FROM users";
        if( $strLike ) {
            $sql .= " WHERE $strLike";
        }
        $sql .= " ORDER BY $sort $order";

        $req = $this->dbManager->db->prepare( $sql );
		if( $req->execute()){
            $listUserData = $req->fetchAll( \PDO::FETCH_ASSOC );

            foreach( $listUserData as $userData){
                $user = new User($userData);
                $listUsers[] = $user;                
            }
            
            return $listUsers;
        } else {
            return null;
        }
    }

    /**
     * Get a user by their login.
     *
     * @param string $login User login.
     * @return User|null User object or null if not found or an error occurs.
     */
    public function getUserByLogin(string $login): ?User
    {
        $sql = 'SELECT * FROM users WHERE login=:login';
        $req = $this->dbManager->db->prepare( $sql );
        if( $req->execute( ['login' => $login] ) ){
           $userData = $req->fetch( \PDO::FETCH_ASSOC );
           if( $userData ){
                $user = new User($userData);
                return $user;
           } else {
            return null;
           }
        } else {
            return null;
        }

    }

    /**
     * Get a user by their ID.
     *
     * @param mixed $id User ID.
     * @return User|null User object or null if not found or an error occurs.
     */
    public function getUserById($id): ?User
    {
        $sql = "SELECT * FROM users WHERE id=:id";
        $req = $this->dbManager->db->prepare( $sql );
        if( $req->execute( ['id' => $id] )){
            $userData = $req->fetch( \PDO::FETCH_ASSOC );
            $user = new User($userData);   
            return $user;
        } else {
            return null;
        }
    }

    /**
     * Update a user's information in the database.
     *
     * @param User $user User object with updated information.
     * @return bool True if the update is successful, false otherwise.
     */
    public function updateUser(User $user): ?bool
    {
        if( $user ) {
            $sql = "UPDATE users 
                    SET 
                        login=:login,
                        password=:password,
                        role=:role,
                        is_active=:is_active
                    WHERE 
                        id=:id";
            $req = $this->dbManager->db->prepare( $sql );
            $state = $req->execute([
                ':id'           => $user->getId(),
                ':login'        => $user->getLogin(),
                ':password'     => $user->getPassword(),
                ':role'         => $user->getRole(),
                ':is_active'    => $user->getIsActive()

            ]);
            return $state;
        }

        return false;
    }

    /**
     * Delete a user by their ID.
     *
     * @param mixed $id User ID.
     * @return bool True if the deletion is successful, false otherwise.
     */
    public function deleteUserById($id): bool
    {
        $sql = "DELETE FROM users WHERE id =:id";
        $req = $this->dbManager->db->prepare($sql);
        if($req->execute(['id' => $id])){
            $rowCount = $req->rowCount();
            return $rowCount > 0;
        } else {
            return false;
        }
    }

    /**
     * Insert a new user into the database.
     *
     * @param User $user User object to be inserted.
     * @return User|null User object with assigned ID if successful, null otherwise.
     */
    public function insertUser(User $user): ?User
    {
        $sql = "INSERT INTO users (
                    login,
                    password,
                    role,
                    is_active
                ) VALUES (
                    :login,
                    :password,
                    :role,
                    :is_active
                )";
        $req = $this->dbManager->db->prepare( $sql );
        $state = $req->execute([
            ':login'        => $user->getLogin(),
            ':password'     => $user->getPassword(),
            ':role'         => $user->getRole(),
            ':is_active'    => $user->getIsActive()
        ]);
        
        if( !$state ) {
            return null;
        } else {
            $idUser = $this->dbManager->db->lastInsertId();
            $user->setId($idUser);
            
            return $user;
        }  
    }
}