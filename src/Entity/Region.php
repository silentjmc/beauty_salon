<?php

namespace App\Entity;

use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Region Entity
 * 
 * This class represents a geographic region that contains departments.
 * It is used for organizing territorial divisions and related statistics.
 */
#[ORM\Entity(repositoryClass: RegionRepository::class)]
class Region
{
    /**
     * Unique identifier for the region
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The name of the region
     */
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * Collection of departments belonging to this region
     * 
     * @var Collection<int, Department>
     */
    #[ORM\OneToMany(targetEntity: Department::class, mappedBy: 'region')]
    private Collection $departments;

    /**
     * Collection of statistics related to this region
     * 
     * @var Collection<int, Statistic>
     */
    #[ORM\OneToMany(mappedBy: 'region', targetEntity: Statistic::class)]
    private Collection $statistics;

    /**
     * Constructor for initializing collections
     */
    public function __construct()
    {
        $this->departments = new ArrayCollection();
        $this->statistics = new ArrayCollection();
    }

    /**
     * Get the unique identifier for this region
     * 
     * @return int|null The ID of the region
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the name of the region
     * 
     * @return string|null The name of the region
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the region
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
     * Get all departments belonging to this region
     * 
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    /**
     * Add a department to this region
     * 
     * This method also updates the inverse side of the relationship.
     * 
     * @param Department $department The department to add
     * @return static Returns the current instance for method chaining
     */
    public function addDepartment(Department $department): static
    {
        if (!$this->departments->contains($department)) {
            $this->departments->add($department);
            $department->setRegion($this);
        }

        return $this;
    }

    /**
     * Remove a department from this region
     * 
     * This method also updates the inverse side of the relationship
     * if necessary.
     * 
     * @param Department $department The department to remove
     * @return static Returns the current instance for method chaining
     */
    public function removeDepartment(Department $department): static
    {
        if ($this->departments->removeElement($department)) {
            // set the owning side to null (unless already changed)
            if ($department->getRegion() === $this) {
                $department->setRegion(null);
            }
        }

        return $this;
    }

    /**
     * Get all statistics related to this region
     * 
     * @return Collection<int, Statistic> Collection of statistics
     */
    public function getStatistics(): Collection
    {
        return $this->statistics;
    }
    
    /**
     * Add a statistic to this region
     * 
     * This method also updates the inverse side of the relationship.
     * 
     * @param Statistic $statistic The statistic to add
     * @return self Returns the current instance for method chaining
     */
    public function addStatistic(Statistic $statistic): self
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics[] = $statistic;
            $statistic->setRegion($this);
        }
    
        return $this;
    }
    
    /**
     * Remove a statistic from this region
     * 
     * This method also updates the inverse side of the relationship
     * if necessary.
     * 
     * @param Statistic $statistic The statistic to remove
     * @return self Returns the current instance for method chaining
     */
    public function removeStatistic(Statistic $statistic): self
    {
        if ($this->statistics->removeElement($statistic)) {
            // set the owning side to null (unless already changed)
            if ($statistic->getRegion() === $this) {
                $statistic->setRegion(null);
            }
        }
    
        return $this;
    }
}
