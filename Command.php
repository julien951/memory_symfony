<?php
// src/Command/AddThemeCommand.php

namespace App\Command;

use App\Entity\Game;
use App\Entity\Theme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Repository\ThemeRepository;

#[AsCommand(name: 'add:theme', description: 'Ajoute un nouveau thème et des images issues de Pixabay à la base de données')]
class ThemeCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;
    private ThemeRepository $repository;

    // On peut externaliser l'API key dans un fichier de configuration
    private const API_KEY = '46302346-f78b4c19a0a403fe2c14eb04d';
    private const PIXABAY_URL = 'https://pixabay.com/api/';

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient, ThemeRepository $repository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->repository = $repository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nom_theme', InputArgument::REQUIRED, 'Nom du thème')
            ->addArgument('search', InputArgument::REQUIRED, 'Recherche Pixabay')
            ->addOption('nb', null, InputOption::VALUE_OPTIONAL, 'Nombre d\'images', 64);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $themeName = $input->getArgument('nom_theme');
        $search = $input->getArgument('search');
        $limit = $input->getOption('nb');

        // Vérifier si le thème existe déjà
        if ($this->themeExists($themeName)) {
            $io->error('Ce thème existe déjà');
            return Command::FAILURE;
        }

        // Créer le nouveau thème
        $theme = $this->createTheme($themeName);

        // Récupérer les images depuis l'API Pixabay
        $images = $this->fetchImagesFromPixabay($search, $limit);

        if (empty($images)) {
            $io->error('Aucune image n\'a été trouvée');
            return Command::FAILURE;
        }

        // Ajouter les images au thème
        $this->addGamesToTheme($images, $theme);

        $this->entityManager->flush();

        $io->success("Le thème '$themeName' a été ajouté avec succès.");

        return Command::SUCCESS;
    }

    private function themeExists(string $themeName): bool
    {
        return $this->repository->findOneBy(['name' => $themeName]) !== null;
    }

    private function createTheme(string $themeName): Theme
    {
        $theme = new Theme();
        $theme->setName($themeName);

        $this->entityManager->persist($theme);

        return $theme;
    }

    private function fetchImagesFromPixabay(string $search, int $limit): array
    {
        $response = $this->httpClient->request('GET', self::PIXABAY_URL, [
            'query' => [
                'key' => self::API_KEY,
                'q' => $search,
                'image_type' => 'photo',
                'per_page' => $limit,
            ]
        ]);

        // Vérification du statut de la réponse HTTP
        if ($response->getStatusCode() !== 200) {
            return [];
        }

        $data = $response->toArray();

        return $data['hits'] ?? [];
    }

    private function addGamesToTheme(array $images, Theme $theme): void
    {
        foreach ($images as $imageData) {
            $game = new Game();
            $game->setLink($imageData['webformatURL']);
            $game->setTheme($theme);

            $this->entityManager->persist($game);
        }
    }
}