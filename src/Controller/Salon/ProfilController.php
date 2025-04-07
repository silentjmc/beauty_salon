<?php

namespace App\Controller\Salon;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use App\Entity\BeautySalon;
use App\Entity\Department;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class ProfilController extends AbstractController{
    #[Route('/api/profile', name: 'app_profile_get', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getProfile(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            $id = $user->getId();
            $email = $user->getEmail();
            $managerFirstName = $user->getManagerFirstName();
            $managerLastName = $user->getManagerLastName();
        } else {
            throw new AccessDeniedException('Access denied');
        }

        $salon = $entityManager->getRepository(BeautySalon::class)->findOneBy(['manager' => $user]);

        if (!$salon) {
            $errorMessages[] = "No beauty salon found for this user";
            return $this->json([
                'message' => 'No beauty salon found for this user.',
            ], 404);
        }

        $salonData = [
            'id' => $salon->getId(),
            'name' => $salon->getName(),
            'street' => $salon->getStreet(),
            'zipCode' => $salon->getZipCode(),
            'city' => $salon->getCity(),
            'openingDate' => $salon->getOpeningDate()->format('Y-m-d'),
            'numberEmployeeFulltime' => $salon->getNumberEmployeeFulltime(),
        ];

        return $this->json([
            'id' => $id,
            'email' => $email,
            'managerFirstName' => $managerFirstName,
            'managerLastName' => $managerLastName,
            'salon' => $salonData,
        ]);
    }

    #[Route('/api/profile', name: 'app_profile_update', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateProfile(
        Request $request, 
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        $errorMessages = [];
        if (!$user) {
            $errorMessages[] = "User not authenticated"; 
        }

        $salon = $entityManager->getRepository(BeautySalon::class)->findOneBy(['manager' => $user]);
        $content = $request->getContent(); 
        $data = json_decode($content, true);
        $validUserFields = ['managerFirstName', 'managerLastName'];
        $validSalonFields = ['name', 'street', 'zipCode', 'city', 'openingDate', 'numberEmployeeFulltime'];
        
        $invalidFields = [];
        $profileUpdate=false;

        // Verifying root fields (excluding salon)
        foreach ($data as $field => $value) {
            if ($field !== 'salon' && !in_array($field, $validUserFields)) {
                $invalidFields[] = $field;
            }
        }

        // Check salon fields if present
        if (isset($data['salon']) && is_array($data['salon'])) {
            foreach ($data['salon'] as $field => $value) {
                if (!in_array($field, $validSalonFields)) {
                    $invalidFields[] = 'salon.' . $field;
                }
            }
        }

        // If there are invalid fields, return an error
        if (!empty($invalidFields)) {
            $errorMessages[] = "Invalid fields detected: " . implode(', ', $invalidFields);
        }

        // if invalid JSON data, return an error
        if (!$data) {
            $errorMessages[] = "Invalid JSON data";
        }

        // Update user and salon fields if present and valid (no null and no empty)
        if ($user instanceof User) {
            if (isset($data['managerFirstName']) && $data['managerFirstName'] !== '' && $data['managerFirstName'] !== null) {
                $user->setManagerFirstName($data['managerFirstName']);
                $profileUpdate=true;
            }
            if (isset($data['managerLastName']) && $data['managerLastName'] !== '' && $data['managerLastName'] !== null) {
                $user->setManagerLastName($data['managerLastName']);
                $profileUpdate=true;
            }
        }
        if (isset($data['salon'])) {
            if (isset($data['salon']['name']) && $data['salon']['name'] !== '' && $data['salon']['name'] !== null) {
                $salon->setName($data['salon']['name']);
                $profileUpdate=true;
            }
            if (isset($data['salon']['street']) && $data['salon']['street'] !== '' && $data['salon']['street'] !== null) {
                $salon->setStreet($data['salon']['street']);
                $profileUpdate=true;
            }
            if (isset($data['salon']['zipCode']) && $data['salon']['zipCode'] !== '' && $data['salon']['zipCode'] !== null) {
                $zipCode = $data['salon']['zipCode'];
                if ($zipCode >= 20000 && $zipCode <= 20199) {
                    $departmentCode = '2A'; // Corse-du-Sud
                } elseif ($zipCode >= 20200 && $zipCode <= 20999) {
                    $departmentCode = '2B'; // Haute-Corse
                } elseif (substr($zipCode, 0, 2) === '97') {
                    $departmentCode = substr($zipCode, 0, 3);
                } else {
                    $departmentCode = substr($zipCode, 0, 2);
                }
        
                $department = $entityManager->getRepository(Department::class)
                    ->findOneBy(['code' => $departmentCode]);
        
                if (!$department) {
                    $errorMessages[] = "No department found for the postcode $zipCode";
                }   
                $salon->setZipCode($zipCode);
                $salon->setDepartment($department);
                $profileUpdate=true;
            }

            if (isset($data['salon']['city']) && $data['salon']['city'] !== '' && $data['salon']['city'] !== null) {
                $salon->setCity($data['salon']['city']);
                $profileUpdate=true;
            }
            if (isset($data['salon']['openingDate']) && $data['salon']['openingDate'] !== '' && $data['salon']['openingDate'] !== null) {
                $openingDate = \DateTime::createFromFormat('Y-m-d', $data['salon']['openingDate']);
                if ($openingDate) {
                    $salon->setOpeningDate($openingDate);
                    $profileUpdate=true;
                }
            }
            if (isset($data['salon']['numberEmployeeFulltime']) && $data['salon']['numberEmployeeFulltime'] !== '' && $data['salon']['numberEmployeeFulltime'] !== null) {
                $salon->setNumberEmployeeFulltime($data['salon']['numberEmployeeFulltime']);
                $profileUpdate=true;
            }
        }
        if (!empty($errorMessages)) {
            return new JsonResponse(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }


        if ($profileUpdate) {
            $entityManager->flush();
            return new JsonResponse(['message' => 'Profile updated successfully', Response::HTTP_CREATED]);
        } else {
            return new JsonResponse(['message' => 'No changes detected'], Response::HTTP_OK);
        }

        
    }
}
