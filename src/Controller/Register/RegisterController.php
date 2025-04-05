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
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class RegisterController extends AbstractController{
    #[Route('/api/register', name: 'app_api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer, ValidatorInterface $validator): JsonResponse {
        // Décoder les données JSON envoyées dans la requête
        $content = $request->getContent(); // Récupère le contenu brut de la requête
        $data = json_decode($content, true);
        $errorMessages = [];
        $requiredFields = [
            'email', 'password', 'firstName', 'lastName',
            'salon.name', 'salon.street', 'salon.zipCode', 'salon.city', 'salon.openingDate', 'salon.numberEmployeeFulltime'
        ];

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

        $department = $entityManager->getRepository(Department::class)
            ->findOneBy(['code' => $departmentCode]);

        if (!$department) {
            $errorMessages[] = "No department found for the postcode $zipCode";
        }           

        $user = new User();
        $user->setEmail($data['email']);
        $user->setManagerFirstName($data['firstName']);
        $user->setManagerLastName($data['lastName']);
        $user->setRoles(['ROLE_USER']);
        $password = $data['password'];
        $passwordErrors = $this->validatePassword($password);
        if (!empty($passwordErrors)) {
            $errorMessages = array_merge($errorMessages, $passwordErrors);
        }

        $hashedPassword = $userPasswordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
        }
        if (!empty($errorMessages)) {
            return new JsonResponse(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

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
        $entityManager->persist($user);
        $entityManager->persist($salon);
        $entityManager->flush();

        $email = (new Email())
            ->from('noreply@jmcarre.com')
            ->to($user->getEmail())
            ->subject('Bienvenue chez BeautyConnect !')
            ->text("Bonjour {$user->getManagerFirstName()},\n\nBienvenue sur notre plateforme. Votre salon \"{$salon->getName()}\" a bien été enregistré.\n\nL’équipe BeautyConnect");

        $mailer->send($email);
    
        return new JsonResponse(['message' => 'User and salon registered successfully'], Response::HTTP_CREATED);
    }

    private function validatePassword(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one digit.';
        }

        if (!preg_match('/[\W_]/', $password)) { 
            $errors[] = 'Password must contain at least one special character.';
        }

        return $errors;
    }
}
