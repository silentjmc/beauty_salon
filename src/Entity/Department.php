<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Department Entity
 * 
 * This entity represents a French administrative department.
 * It contains basic information about the department and its relationships
 * with regions, beauty salons, and statistics.
 */
#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    /**
     * Primary key of the entity
     * 
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Name of the department
     * 
     * @var string|null
     */
    #[ORM\Column(length: 25)]
    private ?string $name = null;

    /**
     * Collection of beauty salons located in this department
     * 
     * @var Collection<int, BeautySalon>
     */
    #[ORM\OneToMany(targetEntity: BeautySalon::class, mappedBy: 'department')]
    private Collection $beautySalons;

    /**
     * Region to which this department belongs
     * 
     * @var Region|null
     */
    #[ORM\ManyToOne(inversedBy: 'departments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Region $region = null;

    /**
     * Collection of statistics related to this department
     * 
     * @var Collection<int, Statistic>
     */
    #[ORM\OneToMany(mappedBy: 'department', targetEntity: Statistic::class)]
    private Collection $statistics;

    /**
     * Official code of the department (e.g., '01', '75', '2A')
     * 
     * @var string|null
     */
    #[ORM\Column(length: 3)]
    private ?string $code = null;

    /**
     * Constructor for Department entity
     * 
     * Initializes the collections of beauty salons and statistics.
     */
    public function __construct()
    {
        $this->beautySalons = new ArrayCollection();
        $this->statistics = new ArrayCollection();
    }

    /**
     * Get the ID of the department
     * 
     * @return int|null The ID of the department
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the name of the department
     * 
     * @return string|null The name of the department
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the department
     * 
     * @param string $name The name to set
     * @return static Returns the current instance for method chaining
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the collection of beauty salons located in this department
     * 
     * @return Collection<int, BeautySalon>
     */
    public function getBeautySalons(): Collection
    {
        return $this->beautySalons;
    }

    /**
     * Add a beauty salon to this department
     * 
     * This method also updates the relationship on the beauty salon side.
     * 
     * @param BeautySalon $beautySalon The beauty salon to add
     * @return static Returns the current instance for method chaining
     */
    public function addBeautySalon(BeautySalon $beautySalon): static
    {
        if (!$this->beautySalons->contains($beautySalon)) {
            $this->beautySalons->add($beautySalon);
            $beautySalon->setDepartment($this);
        }

        return $this;
    }

    /**
     * Remove a beauty salon from this department
     * 
     * If the beauty salon is currently associated with this department,
     * this method will also update the relationship on the beauty salon side.
     * 
     * @param BeautySalon $beautySalon The beauty salon to remove
     * @return static Returns the current instance for method chaining
     */
    public function removeBeautySalon(BeautySalon $beautySalon): static
    {
        if ($this->beautySalons->removeElement($beautySalon)) {
            // set the owning side to null (unless already changed)
            if ($beautySalon->getDepartment() === $this) {
                $beautySalon->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * Get the region to which this department belongs
     * 
     * @return Region|null The region entity
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * Set the region to which this department belongs
     * 
     * @param Region|null $region The region to set
     * @return static Returns the current instance for method chaining
     */
    public function setRegion(?Region $region): static
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get the collection of statistics related to this department
     * 
     * @return Collection<int, Statistic> The collection of statistic entities
     */
    public function getStatistics(): Collection
    {
        return $this->statistics;
    }
    
    /**
     * Add a statistic to this department
     * 
     * This method also updates the relationship on the statistic side.
     * 
     * @param Statistic $statistic The statistic to add
     * @return self Returns the current instance for method chaining
     */
    public function addStatistic(Statistic $statistic): self
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics[] = $statistic;
            $statistic->setDepartment($this);
        }
    
        return $this;
    }
    
    /**
     * Remove a statistic from this department
     * 
     * If the statistic is currently associated with this department,
     * this method will also update the relationship on the statistic side.
     * 
     * @param Statistic $statistic The statistic to remove
     * @return self Returns the current instance for method chaining
     */
    public function removeStatistic(Statistic $statistic): self
    {
        if ($this->statistics->removeElement($statistic)) {
            // set the owning side to null (unless already changed)
            if ($statistic->getDepartment() === $this) {
                $statistic->setDepartment(null);
            }
        }
    
        return $this;
    }

    /**
     * Get the official code of the department
     * 
     * @return string|null The department code (e.g., '01', '75', '2A')
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Set the official code of the department
     * 
     * @param string $code The department code to set
     * @return static Returns the current instance for method chaining
     */
    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }
}
