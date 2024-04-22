<?php

namespace Worga\src\Model\Entity;

use Worga\src\Classes\Hydrate;
use DateTime;

/**
 * Class representing a Client entity.
 */
class Client
{
    /** Properties representing client details */
    private $id = 0;
    private $lastName = 0;
    private $firstName = 0;
    private $address = 0;
    private $phone = 0;
    private $email = 0;
    private $other = 0;
    private $insertedAt = 0;
    private $updatedAt = 0;
    private $user;

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
     * Getters and setters for each property.
     */

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getOther()
    {
        return $this->other ?? null;
    }

    public function setOther($other)
    {
        $this->other = $other ?? null;
    }

    public function getInsertedAt(): ?DateTime
    {
        $insertedAt = new DateTime($this->insertedAt);
        return $insertedAt;
    }

    public function setInsertedAt($insertedAt)
    {
        $this->insertedAt = $insertedAt;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /** Get number phone formatted with leading 0 */
    public function getPhoneFormatted(): string
    {
        $phoneFormatted = "0".$this->phone;
        return $phoneFormatted;
    }

}