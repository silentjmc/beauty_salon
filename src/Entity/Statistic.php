<?php

namespace App\Entity;

use App\Repository\StatisticRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Statistic Entity
 * 
 * This class represents statistical data for income averages by geographic area.
 * It can be associated with both departments and regions and provides time-based income metrics.
 */
#[ORM\Entity(repositoryClass: StatisticRepository::class)]
class Statistic
{
    /**
     * Unique identifier for the statistic record
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The area name or classification this statistic represents
     */
    #[ORM\Column(length: 50)]
    private ?string $area = null;

    /**
     * The year this statistic applies to
     */
    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    /**
     * The month this statistic applies to (1-12)
     */
    #[ORM\Column(nullable: true)]
    private ?int $month = null;

    /**
     * The calculated average income for this area and time period
     */
    #[ORM\Column]
    private ?float $averageIncome = null;

    /**
     * The department this statistic is associated with, if applicable
     */
    #[ORM\ManyToOne(targetEntity: Department::class)]
    private ?Department $department = null;

    /**
     * The region this statistic is associated with, if applicable
     */
    #[ORM\ManyToOne(targetEntity: Region::class)]
    private ?Region $region = null;

    /**
     * Get the unique identifier for this statistic record
     * 
     * @return int|null The ID of the statistic
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the area name or classification
     * 
     * @return string|null The area name
     */
    public function getArea(): ?string
    {
        return $this->area;
    }

    /**
     * Set the area name or classification
     * 
     * @param string $area The area name to set
     * @return static Returns the current instance for method chaining
     */
    public function setArea(string $area): static
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get the year this statistic applies to
     * 
     * @return int|null The year
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * Set the year this statistic applies to
     * 
     * @param int|null $year The year to set
     * @return static Returns the current instance for method chaining
     */
    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get the month this statistic applies to
     * 
     * @return int|null The month (1-12)
     */
    public function getMonth(): ?int
    {
        return $this->month;
    }

    /**
     * Set the month this statistic applies to
     * 
     * @param int|null $month The month to set (1-12)
     * @return static Returns the current instance for method chaining
     */
    public function setMonth(?int $month): static
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get the average income value
     * 
     * @return float|null The average income amount
     */
    public function getAverageIncome(): ?float
    {
        return $this->averageIncome;
    }

    /**
     * Set the average income value
     * 
     * @param float $averageIncome The average income amount to set
     * @return static Returns the current instance for method chaining
     */
    public function setAverageIncome(float $averageIncome): static
    {
        $this->averageIncome = $averageIncome;

        return $this;
    }

    /**
     * Get the department this statistic is associated with
     * 
     * @return Department|null The associated department
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * Set the department this statistic is associated with
     * 
     * @param Department|null $department The department to associate with this statistic
     * @return static Returns the current instance for method chaining
     */
    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get the region this statistic is associated with
     * 
     * @return Region|null The associated region
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * Set the region this statistic is associated with
     * 
     * @param Region|null $region The region to associate with this statistic
     * @return static Returns the current instance for method chaining
     */
    public function setRegion(?Region $region): static
    {
        $this->region = $region;

        return $this;
    }
}
