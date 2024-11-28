<?php

declare(strict_types=1);

namespace App\Controller;

use App\Client\TheMovieDBClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GenreController extends AbstractController
{
    public function __construct(
        private readonly TheMovieDBClient $theMovieDBClient,
    ) {
    }

    #[Route('/_partial/', name: 'app_partial_genre_index')]
    public function index(Request $request): Response
    {
        $currentGenre = $request->query->get('currentGenre');

        return $this->render('Genre/Partial/index.html.twig', [
            'currentGenre' => $currentGenre,
            'genres' => $this->theMovieDBClient->getGenres(),
        ]);
    }
}
