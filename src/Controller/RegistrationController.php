<?php
// src/Controller/RegistrationController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    #[IsGranted('ROLE_ADMIN')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_register');
        }

        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();

        // Filtrer les utilisateurs qui ont le rôle ROLE_USER mais pas ROLE_ADMIN
        $usersWithRoleUserOnly = array_filter($users, function(User $user) {
            return in_array('ROLE_USER', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles());
        });

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'users' => $usersWithRoleUserOnly,
        ]);
    }

    #[Route('/user/delete/{id}', name: 'user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager, Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        // Vérifier le token CSRF
        $submittedToken = $request->request->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('delete_user', $submittedToken))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_register');
    }
    #[Route('/user/update/{id}', name: 'user_update', methods: ['POST'])]
        public function updateUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
        {
            $data = json_decode($request->getContent(), true);

            // Mise à jour des champs
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
            if (isset($data['pseudo'])) {
                $user->setPseudo($data['pseudo']);
            }

            // Validation et persistance
            $entityManager->flush();

            return new Response('Utilisateur mis à jour avec succès.', 200);
        }

}