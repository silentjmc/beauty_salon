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

/**
 * New Income Controller
 * 
 * This controller handles the API endpoint for creating new income records
 * for beauty salons and updating related statistical data.
 */
final class NewIncomeController extends AbstractController{
    /**
     * Create a new income record for the previous month
     * 
     * This endpoint allows salon managers to register income for the previous month.
     * It performs the following operations:
     * - Validates user authentication and salon association
     * - Checks if income has already been submitted for the previous month
     * - Validates the income data provided in the request
     * - Creates a new income record for the salon
     * - Updates statistical average income data for France, the salon's department,
     *   and the salon's region
     * 
     * @param Request $request The HTTP request containing income data in JSON format
     *                         Expected format: {"income": 1234.56}
     * @param EntityManagerInterface $entityManager The Doctrine entity manager for database operations
     * @return JsonResponse The JSON response with the result of the operation or error messages
     * 
     * @throws \Exception If data validation or database operations fail
     * 
     * @Route("/api/new_income", name="app_new_income", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    #[Route('/api/new_income', name: 'app_new_income',  methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function newIncome(Request $request, EntityManagerInterface $entityManager): JsonResponse {
        // Get the authenticated user and verify it's a User instance
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Find the beauty salon managed by the user
        // This ensures the user can only add income for their own salon
        $salon = $entityManager->getRepository(BeautySalon::class)->findOneBy(['manager' => $user]);
        if (!$salon) {
            return new JsonResponse(['error' => 'No beauty salon found for this user'], Response::HTTP_NOT_FOUND);
        }

        // Calculate the previous month and year for income registration
        $now = new \DateTimeImmutable();
        $previousMonth = $now->modify('-1 month');
        $month_income = $previousMonth->format('m');
        $year_income = $previousMonth->format('Y');

        // Check if income has already been submitted for this month
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

        // Parse and validate the income data from the request
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (!$data || !isset($data['income']) || !is_numeric($data['income'])) {
            return new JsonResponse([
                'error' => 'Invalid data. Income amount is required and must be a number'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Create and persist the new income record
        $income = new Income();
        $income->setIncome((float)$data['income']);
        $income->setMonthIncome($month_income);
        $income->setYearIncome($year_income);
        $income->setCreatedAt($now); // Date actuelle
        $income->setBeautySalon($salon);

        $entityManager->persist($income);
        $entityManager->flush();

        // Update statistical averages at different geographical levels
        $this->updateAverageIncomeForArea($entityManager, $income, 'France', $month_income, $year_income);
        $this->updateAverageIncomeForArea($entityManager, $income, 'Department', $month_income, $year_income);
        $this->updateAverageIncomeForArea($entityManager, $income, 'Region', $month_income, $year_income);

        // Return success response with income details
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

   /**
     * Update the average income statistics for a specific area
     * 
     * This method calculates and updates or creates the average income statistics
     * for a given area (France, Department, or Region) based on all salons' income
     * for the specified month and year. If statistics already exist for the area
     * and time period, they are updated; otherwise, a new statistic record is created.
     * 
     * The method handles three levels of statistical aggregation:
     * - National level (France): Average income across all salons in France
     * - Regional level: Average income limited to salons in the specified region
     * - Departmental level: Average income limited to salons in the specified department
     * 
     * @param EntityManagerInterface $entityManager The Doctrine entity manager for database operations
     * @param Income $income The newly created income record that triggered this update
     * @param string $area The area type ('France', 'Department', or 'Region')
     * @param string $month The month number (01-12) for which statistics are being updated
     * @param string $year The year (e.g., '2025') for which statistics are being updated
     * @return void
     * 
     * @throws \Doctrine\ORM\NoResultException If the query returns no results when calculating averages
     * @throws \Doctrine\ORM\NonUniqueResultException If the query returns multiple results unexpectedly
     */
    public function updateAverageIncomeForArea(EntityManagerInterface $entityManager, Income $income, string $area, string $month, string $year): void
    {
        // Get salon details and its associated department and region
        $salon = $income->getBeautySalon();
        $department = $salon->getDepartment();
        $region = $department->getRegion();

        // Set up search criteria based on the area type
        $criteria = [
            'area' => $area,
            'month' => $month,
            'year' => $year
        ];

        // Add specific criteria for department or region
        if ($area === 'Department') {
            $criteria['department'] = $department;
        } elseif ($area === 'Region') {
            $criteria['region'] = $region;
        }

        // Check if statistics for this area and period already exist
        $existingAverageIncome = $entityManager->getRepository(Statistic::class)->findOneBy($criteria);

        // Build query to calculate average income for the area
        $avgIncome = $entityManager->createQueryBuilder()
        ->select('AVG(i.income) as avg')
        ->from(Income::class, 'i')
        ->join('i.beautySalon', 's')
        ->where('i.monthIncome = :monthIncome')
        ->andWhere('i.yearIncome = :yearIncome')
        ->setParameter('monthIncome', $month)
        ->setParameter('yearIncome', $year);

        // Filter by department or region if applicable
        if ($area === 'Department') {
            $avgIncome->andWhere('s.department = :department')
                ->setParameter('department', $department);
        } elseif ($area === 'Region') {
            $avgIncome->join('s.department', 'd')
                ->andWhere('d.region = :region')
                ->setParameter('region', $region);
        }

        // Execute query to get average income
        $avgIncome = $avgIncome->getQuery()->getSingleScalarResult();

         // Update existing statistic or create a new one
        if ($existingAverageIncome) {
            $existingAverageIncome->setAverageIncome($avgIncome);
        } else {
            // Create new statistic record with calculated average
            $stat = new Statistic();
            $stat->setArea($area);
            $stat->setMonth($month);
            $stat->setYear($year);
            $stat->setAverageIncome($avgIncome);
    
            // Set department or region reference if applicable
            if ($area === 'Department') {
                $stat->setDepartment($department);
            } elseif ($area === 'Region') {
                $stat->setRegion($region);
            }
    
            $entityManager->persist($stat);
        }
        // Persist changes to database
        $entityManager->flush();
    }
}