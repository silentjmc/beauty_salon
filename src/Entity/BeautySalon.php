<?php

namespace App\Entity;

use App\Repository\BeautySalonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * BeautySalon Entity
 * 
 * This entity represents a beauty salon in the application.
 * It contains information about the salon, its location, manager and associated incomes.
 */
#[ORM\Entity(repositoryClass: BeautySalonRepository::class)]
class BeautySalon
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
     * Name of the beauty salon
     * 
     * @var string|null
     */
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * Street address of the beauty salon
     * 
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    private ?string $street = null;

    /**
     * Zip code of the beauty salon
     * 
     * @var string|null
     */
    #[ORM\Column(length: 5)]
    private ?string $zipCode = null;

    /**
     * City where the beauty salon is located
     * 
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    private ?string $city = null;

    /**
     * Date when the beauty salon started operating
     * 
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $openingDate = null;

    /**
     * Number of full-time employees working at the beauty salon
     * 
     * @var int|null
     */
    #[ORM\Column]
    private ?int $numberEmployeeFulltime = null;

    /**
     * User who manages this beauty salon
     * 
     * @var User|null
     */
    #[ORM\ManyToOne(inversedBy: 'beautySalons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $manager = null;

    /**
     * Department where the beauty salon is located
     * 
     * @var Department|null
     */
    #[ORM\ManyToOne(inversedBy: 'beautySalons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Department $department = null;

    /**
     * Collection of income records associated with this beauty salon
     * 
     * @var Collection<int, Income>
     */
    #[ORM\OneToMany(targetEntity: Income::class, mappedBy: 'beautySalon')]
    private Collection $incomes;

    /**
     * Constructor for BeautySalon entity
     * 
     * Initializes the income collection.
     */
    public function __construct()
    {
        $this->incomes = new ArrayCollection();
    }

    /**
     * Get the ID of the beauty salon
     * 
     * @return int|null The ID of the beauty salon
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the name of the beauty salon
     * 
     * @return string|null The name of the beauty salon
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the name of the beauty salon
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
     * Get the street address of the beauty salon
     * 
     * @return string|null The street address
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * Set the street address of the beauty salon
     * 
     * @param string $street The street address to set
     * @return static Returns the current instance for method chaining
     */
    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get the postal code of the beauty salon
     * 
     * @return string|null The postal code
     */
    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * Set the postal code of the beauty salon
     * 
     * @param string $zipCode The postal code to set
     * @return static Returns the current instance for method chaining
     */
    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get the city where the beauty salon is located
     * 
     * @return string|null The city name
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Set the city where the beauty salon is located
     * 
     * @param string $city The city name to set
     * @return static Returns the current instance for method chaining
     */
    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the opening date of the beauty salon
     * 
     * @return \DateTimeInterface|null The opening date
     */
    public function getOpeningDate(): ?\DateTimeInterface
    {
        return $this->openingDate;
    }

    /**
     * Set the opening date of the beauty salon
     * 
     * @param \DateTimeInterface $openingDate The opening date to set
     * @return static Returns the current instance for method chaining
     */
    public function setOpeningDate(\DateTimeInterface $openingDate): static
    {
        $this->openingDate = $openingDate;

        return $this;
    }

    /**
     * Get the number of full-time employees at the beauty salon
     * 
     * @return int|null The number of full-time employees
     */
    public function getNumberEmployeeFulltime(): ?int
    {
        return $this->numberEmployeeFulltime;
    }

    /**
     * Set the number of full-time employees at the beauty salon
     * 
     * @param int $numberEmployeeFulltime The number of full-time employees to set
     * @return static Returns the current instance for method chaining
     */
    public function setNumberEmployeeFulltime(int $numberEmployeeFulltime): static
    {
        $this->numberEmployeeFulltime = $numberEmployeeFulltime;

        return $this;
    }

    /**
     * Get the manager of the beauty salon
     * 
     * @return User|null The user who manages this beauty salon
     */
    public function getManager(): ?User
    {
        return $this->manager;
    }

    /**
     * Set the manager of the beauty salon
     * 
     * @param User|null $manager The user to set as manager
     * @return static Returns the current instance for method chaining
     */
    public function setManager(?User $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get the department where the beauty salon is located
     * 
     * @return Department|null The department entity
     */
    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    /**
     * Set the department where the beauty salon is located
     * 
     * @param Department|null $department The department to set
     * @return static Returns the current instance for method chaining
     */
    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection<int, Income>
     * 
     * @return Collection<int, Income> The collection of income entities
     */
    public function getIncomes(): Collection
    {
        return $this->incomes;
    }

    /**
     * Add an income to this beauty salon
     * 
     * This method also updates the relationship on the income side.
     * 
     * @param Income $income The income to add
     * @return static Returns the current instance for method chaining
     */
    public function addIncome(Income $income): static
    {
        if (!$this->incomes->contains($income)) {
            $this->incomes->add($income);
            $income->setBeautySalon($this);
        }

        return $this;
    }

    /**
     * Remove an income from this beauty salon
     * 
     * If the income is currently associated with this beauty salon,
     * this method will also update the relationship on the income side.
     * 
     * @param Income $income The income to remove
     * @return static Returns the current instance for method chaining
     */
    public function removeIncome(Income $income): static
    {
        if ($this->incomes->removeElement($income)) {
            // set the owning side to null (unless already changed)
            if ($income->getBeautySalon() === $this) {
                $income->setBeautySalon(null);
            }
        }

        return $this;
    }
}
