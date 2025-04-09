<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25)]
    private ?string $name = null;

    /**
     * @var Collection<int, BeautySalon>
     */
    #[ORM\OneToMany(targetEntity: BeautySalon::class, mappedBy: 'department')]
    private Collection $beautySalons;

    #[ORM\ManyToOne(inversedBy: 'departments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Region $region = null;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: Statistic::class)]
    private Collection $statistics;

    #[ORM\Column(length: 3)]
    private ?string $code = null;

    public function __construct()
    {
        $this->beautySalons = new ArrayCollection();
        $this->statistics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $beautySalon->setDepartment($this);
        }

        return $this;
    }

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

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): static
    {
        $this->region = $region;

        return $this;
    }

    public function getStatistics(): Collection
    {
        return $this->statistics;
    }
    
    public function addStatistic(Statistic $statistic): self
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics[] = $statistic;
            $statistic->setDepartment($this);
        }
    
        return $this;
    }
    
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }
}
