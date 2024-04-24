<?php

namespace Worga\src\Model\Entity;

use Worga\src\Classes\Hydrate;

/**
 * Class Account representing a client account.
 */
class Account
{
    /** Properties representing account details */
    private $id = 0;
    private $estimatesTotal = 0;
    private $invoicesTotal = 0;
    private $receiptsTotal = 0;
    private $restToInvoice = 0;
    private $restToCash = 0;
    private $client;

    /**
     * Constructor to initialize the Account object with data.
     */
    public function __construct(array $accountData)
    {
        // Call the hydrate method of the Hydrate class to set object properties
        $hydrate = new Hydrate();
        $hydrate->hydrate($accountData, $this);
    }

    /**
     * Getters and setters
     * 
     * Typing amounts in strings to optimize bc math calculations
     */

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getEstimatesTotal(): string
    {
        return $this->estimatesTotal;
    }

    public function setEstimatesTotal(string $estimatesTotal)
    {
        $this->estimatesTotal = $estimatesTotal;
    }

    public function getInvoicesTotal(): string
    {
        return $this->invoicesTotal;
    }

    public function setInvoicesTotal(string $invoicesTotal)
    {
        $this->invoicesTotal = $invoicesTotal;
    }

    public function getReceiptsTotal(): string
    {
        return $this->receiptsTotal;
    }

    public function setReceiptsTotal(string $receiptsTotal)
    {
        $this->receiptsTotal = $receiptsTotal;
    }

    public function getRestToInvoice(): string
    {
        return $this->restToInvoice;
    }

    public function setRestToInvoice(string $restToInvoice)
    {
        $this->restToInvoice = $restToInvoice;
    }

    public function getRestToCash(): string
    {
        return $this->restToCash;
    }

    public function setRestToCash(string $restToCash)
    {
        $this->restToCash = $restToCash;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
    }
}
