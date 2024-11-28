<?php

declare(strict_types=1);

namespace App\Controller;

use App\Client\TheMovieDBClient;
use App\Form\MovieAutocompleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MovieController extends AbstractController
{
    public function __construct(
        private readonly TheMovieDBClient $theMovieDBClient,
    ) {
    }

    #[Route('/', name: 'app_movie_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $movies = $this->theMovieDBClient->getMovies($page);

        return $this->render('Movie/index.html.twig', [
            'movies' => $movies,
            'currentPage' => $page,
            'genres' => $this->theMovieDBClient->getGenres()
        ]);
    }

    #[Route('/genre/{genreId}', name: 'app_movie_show_by_genre')]
    public function showByGenre(Request $request, int $genreId): Response
    {
        $page = $request->query->getInt('page', 1);
        $movies = $this->theMovieDBClient->getMovies($page, $genreId);

        return $this->render('Movie/index.html.twig', [
            'genre' => $this->theMovieDBClient->getGenre($genreId),
            'movies' => $movies,
            'currentPage' => $page,
        ]);
    }

    #[Route('/movie/{movieId}', name: 'app_movie_show')]
    public function show(int $movieId): Response
    {
        return $this->render('Movie/show.html.twig', [
            'movie' => $this->theMovieDBClient->getMovie($movieId),
        ]);
    }

    #[Route('/api/movies/search', name: 'app_api_movies_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('query');

        if (empty($query)) {
            return new JsonResponse([]);
        }

        try {
            $movies = $this->theMovieDBClient->searchMovie($query);
            return new JsonResponse($movies->results);
        } catch (\Exception $e) {
            return new JsonResponse([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/_partial/search-widget', name: 'app_partial_movie_search_widget')]
    public function searchWidget(Request $request): Response
    {
        $form = $this->createForm(MovieAutocompleteType::class);

        return $this->render('Movie/Widget/search.html.twig', [
            'form' => $form,
        ]);
    }
}
