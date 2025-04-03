<?php

namespace App\Entity;

use App\Repository\IncomeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IncomeRepository::class)]
class Income
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $income = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateIncome = null;

    #[ORM\ManyToOne(inversedBy: 'incomes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BeautySalon $beautySalon = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIncome(): ?float
    {
        return $this->income;
    }

    public function setIncome(float $income): static
    {
        $this->income = $income;

        return $this;
    }

    public function getDateIncome(): ?\DateTimeInterface
    {
        return $this->dateIncome;
    }

    public function setDateIncome(\DateTimeInterface $dateIncome): static
    {
        $this->dateIncome = $dateIncome;

        return $this;
    }

    public function getBeautySalon(): ?BeautySalon
    {
        return $this->beautySalon;
    }

    public function setBeautySalon(?BeautySalon $beautySalon): static
    {
        $this->beautySalon = $beautySalon;

        return $this;
    }
}
