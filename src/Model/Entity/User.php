<?php

namespace Worga\src\Model\Entity;

use DateTime;
use Worga\src\Classes\Hydrate;

/**
 * Class representing a User entity.
 */
class User
{
    /** Properties representing user details */ 
    private $id = 0;
    private $login = 0;
    private $password = 0;
    private $role = 0;
    private $createdAt = 0;
    private $updatedAt = 0;
    private $isActive = 0;

    /**
     * Constructor to initialize the User object with data.
     *
     * @param array $userData Data to hydrate the User object.
     */
    public function __construct(array $userData)
    {
        // Call the hydrate method of the Hydrate class to set object properties
        $hydrator = new Hydrate();
        $hydrator->hydrate($userData, $this);
    }

    /**
     * Getter and setter methods for each property
     */
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getRole() 
    {
        return $this->role;
    }

    public function getRoleInFrench()
    {
        if ($this->role === 'admin') {
            return "Administrateur";
        } elseif ($this->role === 'editor') {
            return "Editeur";
        } elseif ($this->role === 'visitor') {
            return "Visiteur";
        }
    }

    public function setRole ($role)
    {
        $this-> role = $role;
    }

    public function getCreatedAt(): ?DateTime
    {
        $createdAt = new DateTime($this->createdAt);
        return $createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        $updatedAt = new DateTime($this->updatedAt);
        return $updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }
}