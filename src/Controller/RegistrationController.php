<?php

namespace App\Controller;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        // If already logged in, redirect to home
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $firstName = $request->request->get('first_name');
            $lastName = $request->request->get('last_name');
            $dateOfBirth = $request->request->get('date_of_birth');
            $gender = $request->request->get('gender');
            $phoneNumber = $request->request->get('phone_number');

            // Validation
            if (!$email || !$password || !$firstName || !$lastName) {
                $this->addFlash('error', 'Tutti i campi obbligatori devono essere compilati.');
                return $this->render('security/register.html.twig');
            }

            // Check if user already exists
            $existingUser = $userRepository->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Un utente con questa email esiste già.');
                return $this->render('security/register.html.twig');
            }

            try {
                // Create new user
                $user = new User();
                $user->setEmail($email);
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setRoles(['ROLE_USER']);

                if ($dateOfBirth) {
                    $user->setDateOfBirth(new \DateTimeImmutable($dateOfBirth));
                }

                if ($gender) {
                    $user->setGender($gender);
                }

                if ($phoneNumber) {
                    $user->setPhoneNumber($phoneNumber);
                }

                // Hash password
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                // Save user
                $userRepository->save($user, true);

                $this->addFlash('success', 'Registrazione completata! Ora puoi effettuare il login.');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore durante la registrazione: ' . $e->getMessage());
            }
        }

        return $this->render('security/register.html.twig');
    }
}
