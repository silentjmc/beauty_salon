<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $managerFirstName = null;

    #[ORM\Column(length: 100)]
    private ?string $managerLastName = null;

    /**
     * @var Collection<int, BeautySalon>
     */
    #[ORM\OneToMany(targetEntity: BeautySalon::class, mappedBy: 'manager')]
    private Collection $beautySalons;

    public function __construct()
    {
        $this->beautySalons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getManagerFirstName(): ?string
    {
        return $this->managerFirstName;
    }

    public function setManagerFirstName(string $managerFirstName): static
    {
        $this->managerFirstName = $managerFirstName;

        return $this;
    }

    public function getManagerLastName(): ?string
    {
        return $this->managerLastName;
    }

    public function setManagerLastName(string $managerLastName): static
    {
        $this->managerLastName = $managerLastName;

        return $this;
    }

    /**
     * @return Collection<int, BeautySalon>
     */
    public function getBeautySalons(): Collection
    {
        return $this->beautySalons;
    }

    public function addBeautySalon(BeautySalon $beautySalon): static
    {
        if (!$this->beautySalons->contains($beautySalon)) {
            $this->beautySalons->add($beautySalon);
            $beautySalon->setManager($this);
        }

        return $this;
    }

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
