<?php

namespace App\Controller\Income;

use App\Entity\BeautySalon;
use App\Entity\Income;
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
}