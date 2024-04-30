<?php

namespace Worga\src\Model\Entity;

use Worga\src\Classes\Hydrate;

use DateTime;

/**
 * Class Document representing a document.
 */
class Document
{
    /** Properties */
    private $id = 0;
    private $name = 0;
    private $path = 0;
    private $insertedAt = 0;
    private $updatedAt = 0;
    private $finTrans;
    private $user;

    /**
     * Constructor to initialize the FinancialTransaction object with data.
     * 
     * @param array $data The data to initialize the FinancialTransaction object with.
     */
    public function __construct(array $data)
    {
        $hydrator = new Hydrate();
        $hydrator->hydrate($data, $this);
    }

    /** Getters and Setters */

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
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

    public function getFinTrans(): ?FinancialTransaction
    {
        return $this->finTrans;
    }

    public function setFinTrans(FinancialTransaction $finTrans)
    {
        $this->finTrans = $finTrans;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}