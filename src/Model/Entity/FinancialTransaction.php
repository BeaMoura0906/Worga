<?php

namespace Worga\src\Model\Entity;

use Worga\src\Classes\Hydrate;
use DateTime;

/**
 * Class FinancialTransaction representing a financial transaction.
 */
class FinancialTransaction
{
    /** Properties */
    private $id = 0;
    private $title = 0;
    private $description = 0;
    private $category = 0;
    private $amountExVat = 0;
    private $vatRate = 0;
    private $finTransDate = 0;
    private $insertedAt = 0;
    private $updatedAt = 0;
    private $account;
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

    /** 
     * Getters and Setters.
     * 
     * Typing amounts and rates in strings to optimize bc math calculations.
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getAmountExVat(): string
    {
        return (string) $this->amountExVat;
    }

    public function setAmountExVat(string $amountExVat)
    {
        $this->amountExVat = $amountExVat;
    }

    public function getVatRate(): string
    {
        return (string) $this->vatRate;
    }

    public function setVatRate(string $vatRate)
    {
        $this->vatRate = $vatRate;
    }

    public function getFinTransDate(): ?DateTime
    {
        $finTransDate = new DateTime($this->finTransDate);
        return $finTransDate;
    }

    public function setFinTransDate($finTransDate)
    {
        $this->finTransDate = $finTransDate;
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

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the amount including VAT. Uses bcmath for calculation.
     * 
     * @param string $amountExVat The amount excluding VAT.
     * @param string $vatRate The VAT rate.
     * @return string The amount including VAT.
     */
    public function getAmountIncVat(string $amountExVat, string $vatRate): ?string
    {
        // Use bcmath for IncVat calculation
        $decimalVat = bcdiv($vatRate, 100, 2);
        $vat = bcmul($amountExVat, $decimalVat, 2);
        $amountIncVat = bcadd($amountExVat, $vat, 2);
        return $amountIncVat;
    }
}