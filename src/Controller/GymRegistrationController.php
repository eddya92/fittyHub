<?php

namespace App\Controller;

use App\Domain\Gym\Entity\Gym;
use App\Domain\Gym\Repository\GymRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class GymRegistrationController extends AbstractController
{
    #[Route('/gym/register', name: 'gym_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepositoryInterface $userRepository,
        GymRepositoryInterface $gymRepository
    ): Response {
        // If already logged in, redirect to home
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            // User data
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $firstName = $request->request->get('first_name');
            $lastName = $request->request->get('last_name');
            $phoneNumber = $request->request->get('phone_number');

            // Gym data
            $gymName = $request->request->get('gym_name');
            $gymDescription = $request->request->get('gym_description');
            $gymAddress = $request->request->get('gym_address');
            $gymCity = $request->request->get('gym_city');
            $gymPostalCode = $request->request->get('gym_postal_code');
            $gymPhoneNumber = $request->request->get('gym_phone_number');
            $gymEmail = $request->request->get('gym_email');

            // Validation
            if (!$email || !$password || !$firstName || !$lastName || !$gymName || !$gymAddress || !$gymCity || !$gymPostalCode) {
                $this->addFlash('error', 'Tutti i campi obbligatori devono essere compilati.');
                return $this->render('gym/register.html.twig');
            }

            // Check if user already exists
            $existingUser = $userRepository->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Un utente con questa email esiste già.');
                return $this->render('gym/register.html.twig');
            }

            try {
                // Create gym admin user
                $user = new User();
                $user->setEmail($email);
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setRoles(['ROLE_ADMIN']); // Admin role for gym owner

                if ($phoneNumber) {
                    $user->setPhoneNumber($phoneNumber);
                }

                // Hash password
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);

                $userRepository->save($user, true);

                // Create gym
                $gym = new Gym();
                $gym->setName($gymName);
                $gym->setDescription($gymDescription ?? '');
                $gym->setAddress($gymAddress);
                $gym->setCity($gymCity);
                $gym->setPostalCode($gymPostalCode);
                $gym->setPhoneNumber($gymPhoneNumber ?? $phoneNumber);
                $gym->setEmail($gymEmail ?? $email);
                $gym->addAdmin($user); // Add user as admin

                $gymRepository->save($gym, true);

                $this->addFlash('success', 'Palestra registrata con successo! Ora puoi effettuare il login come amministratore.');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Errore durante la registrazione: ' . $e->getMessage());
            }
        }

        return $this->render('gym/register.html.twig');
    }
}
