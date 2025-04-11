<?php

namespace App\Controller\Income;

use App\Entity\BeautySalon;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Historical Income Controller
 * 
 * This controller handles API endpoints for retrieving historical income data
 * of beauty salons for authenticated users.
 */
final class HistoricController extends AbstractController{
    /**
     * Get historical income data for the authenticated user's beauty salon
     * 
     * This endpoint retrieves all income records for a beauty salon managed by
     * the authenticated user and returns them in descending chronological order.
     * It performs the following operations:
     * - Validates user authentication and salon association
     * - Retrieves all income records for the salon
     * - Orders income records by year and month (newest first)
     * - Formats the data for API consumption
     * 
     * @param EntityManagerInterface $entityManager The Doctrine entity manager for database operations
     * @return JsonResponse The JSON response containing historical income data or error messages
     * 
     * @throws \Exception If authentication fails or query execution encounters an error
     * 
     * @Route("/api/historic", name="app_historic_get", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    #[Route('/api/historic', name: 'app_historic_get',  methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getHistory(EntityManagerInterface $entityManager): JsonResponse{
      // Get the authenticated user and verify it's a User instance
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Find the beauty salon managed by the user
        // This ensures the user can only access data for their own salon
        $salon = $entityManager->getRepository(BeautySalon::class)->findOneBy(['manager' => $user]);
        if (!$salon) {
            return new JsonResponse(['error' => 'No beauty salon found for this user'], Response::HTTP_NOT_FOUND);
        }

       // Build query to fetch income records
        // We order by year and month descending to show newest entries first
        $queryIncome = $entityManager->createQueryBuilder();
        $queryIncome->select('i')
           ->from('App\Entity\Income', 'i')
           ->where('i.beautySalon = :salon')
           ->setParameter('salon', $salon)
           ->orderBy('i.yearIncome', 'DESC')
           ->addOrderBy('i.monthIncome', 'DESC');

        // Execute query and get results
        $incomes = $queryIncome->getQuery()->getResult();

        // Format income data for API response
        // Extract only the necessary fields from each income entity
        $historyData = [];
        foreach ($incomes as $income) {
            $historyData[] = [
                'id' => $income->getId(),
                'month' => $income->getMonthIncome(),
                'year' => $income->getYearIncome(),
                'income' => $income->getIncome()
            ];
        }

          // Return formatted JSON response with salon info and income history
        return new JsonResponse([
            'salon' => [
                'id' => $salon->getId(),
                'name' => $salon->getName()
            ],
            'history' => $historyData
        ], Response::HTTP_OK);
    }
}

