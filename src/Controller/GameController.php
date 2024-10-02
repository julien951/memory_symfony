<?php

namespace App\Controller;

use App\Entity\Theme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class GameController extends AbstractController
{
    #[Route('/game/theme/{id}', name: 'app_game')]
    public function index(EntityManagerInterface $entityManager, int $id): Response
    {

        $theme = $entityManager->getRepository(Theme::class)->find($id);
       
        return $this->render('game/index.html.twig', [
            'theme' => $theme,
        ]);
    }
}
