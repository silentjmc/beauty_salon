<?php

namespace App\Entity;

use App\Repository\IncomeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Income Entity
 * 
 * This class represents the income data for a beauty salon.
 * It tracks monthly income amounts with associated time information.
 * 
 * @author Your Name
 */
#[ORM\Entity(repositoryClass: IncomeRepository::class)]
class Income
{
    /**
     * Unique identifier for the income record
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The income amount in the default currency
     */
    #[ORM\Column]
    private ?float $income = null;

    /**
     * The beauty salon this income belongs to
     */
    #[ORM\ManyToOne(inversedBy: 'incomes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BeautySalon $beautySalon = null;

    /**
     * The timestamp when this income record was created
     */
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * The month number (1-12) this income is associated with
     */
    #[ORM\Column]
    private ?int $monthIncome = null;

    /**
     * The year this income is associated with
     */
    #[ORM\Column]
    private ?int $yearIncome = null;

    /**
     * Get the unique identifier for this income record
     * 
     * @return int|null The ID of the income record
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the income amount
     * 
     * @return float|null The income amount
     */
    public function getIncome(): ?float
    {
        return $this->income;
    }

    /**
     * Set the income amount
     * 
     * @param float $income The income amount to set
     * @return static Returns the current instance for method chaining
     */
    public function setIncome(float $income): static
    {
        $this->income = $income;

        return $this;
    }

    /**
     * Get the beauty salon this income belongs to
     * 
     * @return BeautySalon|null The associated beauty salon
     */
    public function getBeautySalon(): ?BeautySalon
    {
        return $this->beautySalon;
    }

    /**
     * Set the beauty salon this income belongs to
     * 
     * @param BeautySalon|null $beautySalon The beauty salon to associate with this income
     * @return static Returns the current instance for method chaining
     */
    public function setBeautySalon(?BeautySalon $beautySalon): static
    {
        $this->beautySalon = $beautySalon;

        return $this;
    }

    /**
     * Get the creation timestamp of this income record
     * 
     * @return \DateTimeImmutable|null The creation timestamp
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Set the creation timestamp of this income record
     * 
     * @param \DateTimeImmutable $createdAt The timestamp to set
     * @return static Returns the current instance for method chaining
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the month this income is associated with
     * 
     * @return int|null The month number (1-12)
     */
    public function getMonthIncome(): ?int
    {
        return $this->monthIncome;
    }

    /**
     * Set the month this income is associated with
     * 
     * @param int $monthIncome The month number (1-12)
     * @return static Returns the current instance for method chaining
     */
    public function setMonthIncome(int $monthIncome): static
    {
        $this->monthIncome = $monthIncome;

        return $this;
    }

    /**
     * Get the year this income is associated with
     * 
     * @return int|null The year
     */
    public function getYearIncome(): ?int
    {
        return $this->yearIncome;
    }

    /**
     * Set the year this income is associated with
     * 
     * @param int $yearIncome The year to set
     * @return static Returns the current instance for method chaining
     */
    public function setYearIncome(int $yearIncome): static
    {
        $this->yearIncome = $yearIncome;

        return $this;
    }
}
