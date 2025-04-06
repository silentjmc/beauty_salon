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

final class HistoricController extends AbstractController{
    #[Route('/api/historic', name: 'app_historic_get')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getHistory(EntityManagerInterface $entityManager): JsonResponse{
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        $salon = $entityManager->getRepository(BeautySalon::class)->findOneBy(['manager' => $user]);
        if (!$salon) {
            return new JsonResponse(['error' => 'No beauty salon found for this user'], Response::HTTP_NOT_FOUND);
        }

        $queryIncome = $entityManager->createQueryBuilder();
        $queryIncome->select('i')
           ->from('App\Entity\Income', 'i')
           ->where('i.beautySalon = :salon')
           ->setParameter('salon', $salon)
           ->orderBy('i.dateIncome', 'DESC');

        $incomes = $queryIncome->getQuery()->getResult();

        $historyData = [];
        foreach ($incomes as $income) {
            $historyData[] = [
                'id' => $income->getId(),
                'date' => $income->getDateIncome(),
                'amount' => $income->getAmount()
            ];
        }
        return new JsonResponse([
            'salon' => [
                'id' => $salon->getId(),
                'name' => $salon->getName()
            ],
            'history' => $historyData
        ], Response::HTTP_OK);
    }
}

