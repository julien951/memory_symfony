<?php
// src/Security/LoginSuccessHandler.php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        // Récupérer les rôles de l'utilisateur connecté
        $roles = $token->getUser()->getRoles();

        // Redirection en fonction des rôles
        if (in_array('ROLE_ADMIN', $roles, true)) {
            // Redirection vers la page d'enregistrement pour l'admin
            return new RedirectResponse($this->router->generate('app_register'));
        }

        // Redirection vers la page lucky/number pour les utilisateurs normaux
        return new RedirectResponse($this->router->generate('app_lucky_number'));
    }
}
