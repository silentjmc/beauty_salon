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
/*
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateIncome = null;
*/
    #[ORM\ManyToOne(inversedBy: 'incomes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BeautySalon $beautySalon = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?int $monthIncome = null;

    #[ORM\Column]
    private ?int $yearIncome = null;

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
/*
    public function getDateIncome(): ?\DateTimeInterface
    {
        return $this->dateIncome;
    }

    public function setDateIncome(\DateTimeInterface $dateIncome): static
    {
        $this->dateIncome = $dateIncome;

        return $this;
    }
*/
    public function getBeautySalon(): ?BeautySalon
    {
        return $this->beautySalon;
    }

    public function setBeautySalon(?BeautySalon $beautySalon): static
    {
        $this->beautySalon = $beautySalon;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getMonthIncome(): ?int
    {
        return $this->monthIncome;
    }

    public function setMonthIncome(int $monthIncome): static
    {
        $this->monthIncome = $monthIncome;

        return $this;
    }

    public function getYearIncome(): ?int
    {
        return $this->yearIncome;
    }

    public function setYearIncome(int $yearIncome): static
    {
        $this->yearIncome = $yearIncome;

        return $this;
    }
}
