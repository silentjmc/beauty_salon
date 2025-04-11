<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User Entity
 * 
 * This class represents a user in the system, specifically beauty salon managers.
 * It implements Symfony's security interfaces for authentication and authorization.
 * Each user can manage multiple beauty salons.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'This email is already in use.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Unique identifier for the user
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Email address of the user, used as the username for authentication
     * Must be unique across all users
     */
    #[ORM\Column(length: 180)]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    #[Assert\NotBlank(message: "Email cannot be empty.")]
    private ?string $email = null;

    /**
     * The security roles assigned to this user
     * 
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * The hashed password for authentication
     * 
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * First name of the manager
     */
    #[ORM\Column(length: 100)]
    private ?string $managerFirstName = null;

    /**
     * Last name of the manager
     */
    #[ORM\Column(length: 100)]
    private ?string $managerLastName = null;

    /**
     * Collection of beauty salons managed by this user
     * 
     * @var Collection<int, BeautySalon>
     */
    #[ORM\OneToMany(targetEntity: BeautySalon::class, mappedBy: 'manager')]
    private Collection $beautySalons;

    /**
     * Constructor initializes collections
     */
    public function __construct()
    {
        $this->beautySalons = new ArrayCollection();
    }

    /**
     * Get the unique identifier for this user
     * 
     * @return int|null The ID of the user
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the email address of the user
     * 
     * @return string|null The email address
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the email address for the user
     * 
     * @param string $email The email to set
     * @return static Returns the current instance for method chaining
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     * Returns the email address as the user identifier for Symfony security.
     *
     * @see UserInterface The user identifier
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Returns the roles granted to the user.
     * 
     * Automatically includes ROLE_USER for all users and ensures the array
     * contains unique values.
     * 
     * @see UserInterface
     *
     * @return list<string> The user roles
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * Set the security roles for this user
     * 
     * @param list<string> $roles The roles to assign
     * @return static Returns the current instance for method chaining
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Returns the hashed password used for authentication
     * 
     * @see PasswordAuthenticatedUserInterface
     * @return string|null The hashed password
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the password for this user
     * Note: This should be a hashed password, not a plain text password
     * 
     * @param string $password The hashed password to set
     * @return static Returns the current instance for method chaining
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Removes sensitive data from the user.
     * 
     * Called when authentication is completed to ensure sensitive data
     * (like plain password if temporarily stored) is removed.
     * 
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Get the manager's first name
     * 
     * @return string|null The first name
     */
    public function getManagerFirstName(): ?string
    {
        return $this->managerFirstName;
    }

    /**
     * Set the manager's first name
     * 
     * @param string $managerFirstName The first name to set
     * @return static Returns the current instance for method chaining
     */
    public function setManagerFirstName(string $managerFirstName): static
    {
        $this->managerFirstName = $managerFirstName;

        return $this;
    }

    /**
     * Get the manager's last name
     * 
     * @return string|null The last name
     */
    public function getManagerLastName(): ?string
    {
        return $this->managerLastName;
    }

    /**
     * Set the manager's last name
     * 
     * @param string $managerLastName The last name to set
     * @return static Returns the current instance for method chaining
     */
    public function setManagerLastName(string $managerLastName): static
    {
        $this->managerLastName = $managerLastName;

        return $this;
    }

    /**
     * Get all beauty salons managed by this user
     * 
     * @return Collection<int, BeautySalon>
     */
    public function getBeautySalons(): Collection
    {
        return $this->beautySalons;
    }

    /**
     * Add a beauty salon to this user's management
     * 
     * This method also updates the inverse side of the relationship.
     * 
     * @param BeautySalon $beautySalon The beauty salon to add
     * @return static Returns the current instance for method chaining
     */
    public function addBeautySalon(BeautySalon $beautySalon): static
    {
        if (!$this->beautySalons->contains($beautySalon)) {
            $this->beautySalons->add($beautySalon);
            $beautySalon->setManager($this);
        }

        return $this;
    }

    /**
     * Remove a beauty salon from this user's management
     * 
     * This method also updates the inverse side of the relationship
     * if necessary.
     * 
     * @param BeautySalon $beautySalon The beauty salon to remove
     * @return static Returns the current instance for method chaining
     */
    public function removeBeautySalon(BeautySalon $beautySalon): static
    {
        if ($this->beautySalons->removeElement($beautySalon)) {
            // set the owning side to null (unless already changed)
            if ($beautySalon->getManager() === $this) {
                $beautySalon->setManager(null);
            }
        }

        return $this;
    }
}
