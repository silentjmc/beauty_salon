<?php

namespace App\Controller\Register;

use App\Entity\User;
use App\Entity\BeautySalon;
use App\Entity\Department;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Register Controller
 * 
 * This controller handles the API endpoint for registering new users as beauty salon
 * managers. It processes user and salon information, validates the data, creates
 * the necessary entities, and sends a welcome email to the newly registered user.
 * 
 */
final class RegisterController extends AbstractController{
    /**
     * Register a new user and their associated beauty salon
     * 
     * This endpoint processes the registration of a new salon manager and their beauty salon.
     * It handles validation, department lookup, and entity creation.
     * 
     * @param Request $request The HTTP request containing user and salon data
     * @param UserPasswordHasherInterface $userPasswordHasher The password hashing service
     * @param EntityManagerInterface $entityManager The Doctrine entity manager
     * @param MailerInterface $mailer The mailer service for sending emails
     * @param ValidatorInterface $validator The validator service for entity validation
     * @return JsonResponse The JSON response with the result of the operation
     * 
     * @throws \Exception If data validation or operations fail
     */
    #[Route('/api/register', name: 'app_api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer, ValidatorInterface $validator): JsonResponse {
        // Decode the JSON data sent in the request
        $content = $request->getContent(); 
        $data = json_decode($content, true);
        $errorMessages = [];

        // Define all required fields for the registration
        $requiredFields = [
            'email', 'password', 'firstName', 'lastName',
            'salon.name', 'salon.street', 'salon.zipCode', 'salon.city', 'salon.openingDate', 'salon.numberEmployeeFulltime'
        ];

        // Validate that all required fields are present and not empty
        foreach ($requiredFields as $field) {
            $keys = explode('.', $field);
            $value = $data;
            foreach ($keys as $key) {
                if (!isset($value[$key]) || $value[$key] === '' || $value[$key] === null) {
                    $errorMessages[] = "$field is missing or empty";
                }
                $value = $value[$key];
            }
        }

        // Determine department code from salon's zip code
        // Special handling for Corsica and overseas departments
        $salonData = $data['salon'];   
        $zipCode = $salonData['zipCode'];
        if ($zipCode >= 20000 && $zipCode <= 20199) {
            $departmentCode = '2A'; // Corse-du-Sud
        } elseif ($zipCode >= 20200 && $zipCode <= 20999) {
            $departmentCode = '2B'; // Haute-Corse
        } elseif (substr($zipCode, 0, 2) === '97') {
            $departmentCode = substr($zipCode, 0, 3);
        } else {
            $departmentCode = substr($zipCode, 0, 2);
        }

        // Find the department based on the code
        $department = $entityManager->getRepository(Department::class)
            ->findOneBy(['code' => $departmentCode]);

        if (!$department) {
            $errorMessages[] = "No department found for the postcode $zipCode";
        }           

        // Create and set up user entity
        $user = new User();
        $user->setEmail($data['email']);
        $user->setManagerFirstName($data['firstName']);
        $user->setManagerLastName($data['lastName']);
        $user->setRoles(['ROLE_USER']);

        // Validate and hash the password
        $password = $data['password'];
        $passwordErrors = $this->validatePassword($password);
        if (!empty($passwordErrors)) {
            $errorMessages = array_merge($errorMessages, $passwordErrors);
        }
        $hashedPassword = $userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

         // Perform entity validation using Symfony validator
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
        }

        // Return error response if any validation failed
        if (!empty($errorMessages)) {
            return new JsonResponse(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Create and set up the salon entity
        $salonData = $data['salon'];
        $salon = new BeautySalon();
        $salon->setName($salonData['name']);
        $salon->setStreet($salonData['street']);
        $salon->setZipCode($salonData['zipCode']);
        $salon->setCity($salonData['city']);
        $salon->setOpeningDate(new \DateTime($salonData['openingDate']));
        $salon->setNumberEmployeeFulltime($salonData['numberEmployeeFulltime']);
        $salon->setManager($user);
        $salon->setDepartment($department);

        // Persist both entities to database
        $entityManager->persist($user);
        $entityManager->persist($salon);
        $entityManager->flush();

        // Send welcome email to the new user
        $email = (new Email())
            ->from('noreply@jmcarre.com')
            ->to($user->getEmail())
            ->subject('Bienvenue chez BeautyConnect !')
            ->text("Bonjour {$user->getManagerFirstName()},\n\nBienvenue sur notre plateforme. Votre salon \"{$salon->getName()}\" a bien été enregistré.\n\nL’équipe BeautyConnect");

        $mailer->send($email);

        // Return success response
        return new JsonResponse(['message' => 'User and salon registered successfully'], Response::HTTP_CREATED);
    }

    /**
     * Validate password complexity requirements
     * 
     * This method checks if a password meets the security requirements.
     * 
     * @param string $password The password to validate
     * @return array An array of error messages if requirements are not met
     */
    private function validatePassword(string $password): array
    {
        $errors = [];

        // Check minimum length requirement
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        // Check for at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one digit.';
        }

        // Check for at least one special character
        if (!preg_match('/[\W_]/', $password)) { 
            $errors[] = 'Password must contain at least one special character.';
        }

        return $errors;
    }
}
