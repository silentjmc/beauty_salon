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

/**
 * Controller responsible for managing beauty salon profile operations
 * 
 * This controller handles operations related to retrieving and updating
 * profile information for beauty salon managers including their personal
 * details and salon information.
 */
final class ProfilController extends AbstractController{
    /**
     * Retrieves the profile information of the authenticated user
     * 
     * This method returns the profile information of the authenticated user,
     * including their personal details and information about the beauty salon
     * they manage.
     * 
     * @param EntityManagerInterface $entityManager The Doctrine entity manager
     * @return JsonResponse Profile data as JSON or error message if not found
     * 
     * @throws AccessDeniedException If the user is not authenticated
     */
    #[Route('/api/profile', name: 'app_profile_get', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getProfile(EntityManagerInterface $entityManager): JsonResponse
    {
        // Get the currently authenticated user
        $user = $this->getUser();

        // Extract user information if authenticated
        if ($user instanceof User) {
            $id = $user->getId();
            $email = $user->getEmail();
            $managerFirstName = $user->getManagerFirstName();
            $managerLastName = $user->getManagerLastName();
        } else {
            throw new AccessDeniedException('Access denied');
        }

        // Retrieve the beauty salon associated with the user
        $salon = $entityManager->getRepository(BeautySalon::class)->findOneBy(['manager' => $user]);

        // Return 404 error if no salon is found for the user
        if (!$salon) {
            $errorMessages[] = "No beauty salon found for this user";
            return $this->json([
                'message' => 'No beauty salon found for this user.',
            ], 404);
        }

        // Prepare salon data for JSON response
        $salonData = [
            'id' => $salon->getId(),
            'name' => $salon->getName(),
            'street' => $salon->getStreet(),
            'zipCode' => $salon->getZipCode(),
            'city' => $salon->getCity(),
            'openingDate' => $salon->getOpeningDate()->format('Y-m-d'),
            'numberEmployeeFulltime' => $salon->getNumberEmployeeFulltime(),
        ];

        // Return user and salon data as JSON
        return $this->json([
            'id' => $id,
            'email' => $email,
            'managerFirstName' => $managerFirstName,
            'managerLastName' => $managerLastName,
            'salon' => $salonData,
        ]);
    }

    /**
     * Updates the profile information of the authenticated user
     * 
     * This method processes a PATCH request to update user and salon information.
     * It validates the input data and updates only the fields that are provided
     * and valid. For zip code updates, it also determines and updates the
     * associated department.
     * 
     * @param Request $request The HTTP request object containing the data to update
     * @param EntityManagerInterface $entityManager The Doctrine entity manager
     * @return JsonResponse Success message or error details
     */
    #[Route('/api/profile', name: 'app_profile_update', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateProfile(
        Request $request, 
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Get the currently authenticated user
        $user = $this->getUser();
        $errorMessages = [];

        // Check if user is authenticated
        if (!$user) {
            $errorMessages[] = "User not authenticated"; 
        }

        // Retrieve the beauty salon associated with the user
        $salon = $entityManager->getRepository(BeautySalon::class)->findOneBy(['manager' => $user]);

        // Parse the request JSON data
        $content = $request->getContent(); 
        $data = json_decode($content, true);

        // Define valid fields for validation
        $validUserFields = ['managerFirstName', 'managerLastName'];
        $validSalonFields = ['name', 'street', 'zipCode', 'city', 'openingDate', 'numberEmployeeFulltime'];
        
        $invalidFields = [];
        $profileUpdate=false;

         // Validate root fields (excluding salon)
        foreach ($data as $field => $value) {
            if ($field !== 'salon' && !in_array($field, $validUserFields)) {
                $invalidFields[] = $field;
            }
        }

        // Validate salon fields if present
        if (isset($data['salon']) && is_array($data['salon'])) {
            foreach ($data['salon'] as $field => $value) {
                if (!in_array($field, $validSalonFields)) {
                    $invalidFields[] = 'salon.' . $field;
                }
            }
        }

        // Return error if invalid fields are detected
        if (!empty($invalidFields)) {
            $errorMessages[] = "Invalid fields detected: " . implode(', ', $invalidFields);
        }

        // Return error if JSON data is invali
        if (!$data) {
            $errorMessages[] = "Invalid JSON data";
        }

        // Update user fields if present and valid
        if ($user instanceof User) {
            // Update first name if provided and not empty
            if (isset($data['managerFirstName']) && $data['managerFirstName'] !== '' && $data['managerFirstName'] !== null) {
                $user->setManagerFirstName($data['managerFirstName']);
                $profileUpdate=true;
            }

            // Update last name if provided and not empty
            if (isset($data['managerLastName']) && $data['managerLastName'] !== '' && $data['managerLastName'] !== null) {
                $user->setManagerLastName($data['managerLastName']);
                $profileUpdate=true;
            }
        }

        // Update salon fields if present
        if (isset($data['salon'])) {
            // Update salon name if provided and not empty
            if (isset($data['salon']['name']) && $data['salon']['name'] !== '' && $data['salon']['name'] !== null) {
                $salon->setName($data['salon']['name']);
                $profileUpdate=true;
            }
            // Update street address if provided and not empty
            if (isset($data['salon']['street']) && $data['salon']['street'] !== '' && $data['salon']['street'] !== null) {
                $salon->setStreet($data['salon']['street']);
                $profileUpdate=true;
            }
            // Update zip code if provided and not empty
            if (isset($data['salon']['zipCode']) && $data['salon']['zipCode'] !== '' && $data['salon']['zipCode'] !== null) {
                $zipCode = $data['salon']['zipCode'];
                // Determine department code based on zip code
                if ($zipCode >= 20000 && $zipCode <= 20199) {
                    $departmentCode = '2A'; // Corse-du-Sud
                } elseif ($zipCode >= 20200 && $zipCode <= 20999) {
                    $departmentCode = '2B'; // Haute-Corse
                } elseif (substr($zipCode, 0, 2) === '97') {
                    $departmentCode = substr($zipCode, 0, 3);
                } else {
                    $departmentCode = substr($zipCode, 0, 2);
                }
        
                // Find the department by code
                $department = $entityManager->getRepository(Department::class)
                    ->findOneBy(['code' => $departmentCode]);
        
                // Return error if no department found for the zip code
                if (!$department) {
                    $errorMessages[] = "No department found for the postcode $zipCode";
                }
                
                // Update zip code and department
                $salon->setZipCode($zipCode);
                $salon->setDepartment($department);
                $profileUpdate=true;
            }

            // Update city if provided and not empty
            if (isset($data['salon']['city']) && $data['salon']['city'] !== '' && $data['salon']['city'] !== null) {
                $salon->setCity($data['salon']['city']);
                $profileUpdate=true;
            }

            // Update opening date if provided and not empty
            if (isset($data['salon']['openingDate']) && $data['salon']['openingDate'] !== '' && $data['salon']['openingDate'] !== null) {
                $openingDate = \DateTime::createFromFormat('Y-m-d', $data['salon']['openingDate']);
                if ($openingDate) {
                    $salon->setOpeningDate($openingDate);
                    $profileUpdate=true;
                }
            }

            // Update number of full-time employees if provided and not empty
            if (isset($data['salon']['numberEmployeeFulltime']) && $data['salon']['numberEmployeeFulltime'] !== '' && $data['salon']['numberEmployeeFulltime'] !== null) {
                $salon->setNumberEmployeeFulltime($data['salon']['numberEmployeeFulltime']);
                $profileUpdate=true;
            }
        }

        // Return error messages if any
        if (!empty($errorMessages)) {
            return new JsonResponse(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Save changes if any updates were made
        if ($profileUpdate) {
            $entityManager->flush();
            return new JsonResponse(['message' => 'Profile updated successfully', Response::HTTP_CREATED]);
        } else {
            return new JsonResponse(['message' => 'No changes detected'], Response::HTTP_OK);
        }

        
    }
}
