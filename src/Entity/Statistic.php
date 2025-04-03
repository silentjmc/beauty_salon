<?php

namespace App\Entity;

use App\Repository\StatisticRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatisticRepository::class)]
class Statistic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $area = null;

    #[ORM\Column(nullable: true)]
    private ?int $year = null;

    #[ORM\Column(nullable: true)]
    private ?int $month = null;

    #[ORM\Column]
    private ?float $averageIncome = null;

    #[ORM\Column]
    private ?int $countSalon = null;

    #[ORM\OneToOne(inversedBy: 'statistic', cascade: ['persist', 'remove'])]
    private ?Department $department = null;

    #[ORM\OneToOne(inversedBy: 'statistic', cascade: ['persist', 'remove'])]
    private ?Region $region = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArea(): ?string
    {
        return $this->area;
    }

    public function setArea(string $area): static
    {
        $this->area = $area;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(?int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): static
    {
        $this->month = $month;

        return $this;
    }

    public function getAverageIncome(): ?float
    {
        return $this->averageIncome;
    }

    public function setAverageIncome(float $averageIncome): static
    {
        $this->averageIncome = $averageIncome;

        return $this;
    }

    public function getCountSalon(): ?int
    {
        return $this->countSalon;
    }

    public function setCountSalon(int $countSalon): static
    {
        $this->countSalon = $countSalon;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

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
}
