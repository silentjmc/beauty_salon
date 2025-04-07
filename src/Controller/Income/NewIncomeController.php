<?php

namespace App\Controller\Income;

use App\Entity\BeautySalon;
use App\Entity\Income;
use App\Entity\Statistic;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class NewIncomeController extends AbstractController{
    #[Route('/api/new_income', name: 'app_new_income',  methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function newIncome(Request $request, EntityManagerInterface $entityManager): JsonResponse {
        // Get the current user
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Verify if the user is a manager of a beauty salon
        $salon = $entityManager->getRepository(BeautySalon::class)->findOneBy(['manager' => $user]);
        if (!$salon) {
            return new JsonResponse(['error' => 'No beauty salon found for this user'], Response::HTTP_NOT_FOUND);
        }

        // Get the current date and calculate the previous month
        $now = new \DateTimeImmutable();
        $previousMonth = $now->modify('-1 month');
        $month_income = $previousMonth->format('m');
        $year_income = $previousMonth->format('Y');

        // Verify if the income for the previous month already exists
        $existingIncome = $entityManager->getRepository(Income::class)->findOneBy([
            'beautySalon' => $salon,
            'monthIncome' => $month_income,
            'yearIncome' => $year_income
        ]);

        if ($existingIncome) {
            return new JsonResponse([
                'error' => 'Income already submitted for ' . $previousMonth->format('m/Y')
            ], Response::HTTP_CONFLICT);
        }

        $content = $request->getContent();
        $data = json_decode($content, true);

        // Verify if the data is valid
        if (!$data || !isset($data['income']) || !is_numeric($data['income'])) {
            return new JsonResponse([
                'error' => 'Invalid data. Income amount is required and must be a number'
            ], Response::HTTP_BAD_REQUEST);
        }

        //Create a new income entry
        $income = new Income();
        $income->setIncome((float)$data['income']);
        $income->setMonthIncome($month_income);
        $income->setYearIncome($year_income);
        $income->setCreatedAt($now); // Date actuelle
        $income->setBeautySalon($salon);

        $entityManager->persist($income);
        $entityManager->flush();

        // Update the average income for France, salon's department, and salon's region
        $this->updateAverageIncomeForArea($entityManager, $income, 'France', $month_income, $year_income);
        $this->updateAverageIncomeForArea($entityManager, $income, 'Department', $month_income, $year_income);
        $this->updateAverageIncomeForArea($entityManager, $income, 'Region', $month_income, $year_income);

        return new JsonResponse([
            'message' => 'Income successfully recorded for ' . $previousMonth->format('m/Y'),
            'income' => [
                'id' => $income->getId(),
                'amount' => $income->getIncome(),
                'month' => $income->getMonthIncome(),
                'year' => $income->getYearIncome(),
                'created_at' => $income->getCreatedAt()->format('Y-m-d H:i:s'),
                'salon' => $salon->getName()
            ]
        ], Response::HTTP_CREATED);
    }

    // This method updates the average income for a specific area (France, Department, or Region)
    public function updateAverageIncomeForArea(EntityManagerInterface $entityManager, Income $income, string $area, string $month, string $year): void
    {
        // Get the beauty salon, department, and region from the income entity
        $salon = $income->getBeautySalon();
        $department = $salon->getDepartment();
        $region = $department->getRegion();

        //List of criteria to find if the average income exists or not
        $criteria = [
            'area' => $area,
            'month' => $month,
            'year' => $year
        ];

        if ($area === 'Department') {
            $criteria['department'] = $department;
        } elseif ($area === 'Region') {
            $criteria['region'] = $region;
        }

        $existingAverageIncome = $entityManager->getRepository(Statistic::class)->findOneBy($criteria);

        // Calculate the average income for the specified area
        $avgIncome = $entityManager->createQueryBuilder()
        ->select('AVG(i.income) as avg')
        ->from(Income::class, 'i')
        ->join('i.beautySalon', 's')
        ->where('i.monthIncome = :monthIncome')
        ->andWhere('i.yearIncome = :yearIncome')
        ->setParameter('monthIncome', $month)
        ->setParameter('yearIncome', $year);

        if ($area === 'Department') {
            $avgIncome->andWhere('s.department = :department')
                ->setParameter('department', $department);
        } elseif ($area === 'Region') {
            $avgIncome->join('s.department', 'd')
                ->andWhere('d.region = :region')
                ->setParameter('region', $region);
        }

        $avgIncome = $avgIncome->getQuery()->getSingleScalarResult();

        // Update or create the average income entry
        if ($existingAverageIncome) {
            $existingAverageIncome->setAverageIncome($avgIncome);
        } else {
            $stat = new Statistic();
            $stat->setArea($area);
            $stat->setMonth($month);
            $stat->setYear($year);
            $stat->setAverageIncome($avgIncome);
    
            if ($area === 'Department') {
                $stat->setDepartment($department);
            } elseif ($area === 'Region') {
                $stat->setRegion($region);
            }
    
            $entityManager->persist($stat);
        }
    
        $entityManager->flush();
    }
}