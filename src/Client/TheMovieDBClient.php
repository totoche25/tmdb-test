<?php

declare(strict_types=1);

namespace App\Client;

use App\DTO\GenreDTO;
use App\DTO\MovieCollectionDTO;
use App\DTO\MovieDTO;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TheMovieDBClient
{
    private const API_VERSION = '3';

    public function __construct(
        private readonly HttpClientInterface $tmdbClient,
    ) {
    }

    public function request(string $endpoint, array $options = []): array
    {
        try {
            $response = $this->tmdbClient->request('GET', DIRECTORY_SEPARATOR . self::API_VERSION . $endpoint, [
                'query' => $options,
            ]);

            return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (ClientExceptionInterface $exception) {
            return [];
        }
    }

    public function getGenres(): array
    {
        $response = $this->request('/genre/movie/list');
        if (!array_key_exists('genres', $response)) {
            throw new \LogicException('The response from The Movie DB is invalid');
        }

        return array_map(
            static fn (array $genre) => GenreDTO::fromArray($genre),
            $response['genres']
        );
    }

    public function getGenre(int $genreId): GenreDTO
    {
        $response = $this->request("/genre/{$genreId}");
        if (empty($response)) {
            throw new NotFoundHttpException("Genre not found with ID: {$genreId}");
        }

        return GenreDTO::fromArray($response);
    }

    public function getMovies(int $page = 1, ?int $genreId = null): MovieCollectionDTO
    {
        $options = [
            'sort_by' => 'vote_average.desc',
            'vote_count.gte' => 1000,
            'vote_average.gte' => 7,
            'page' => $page,
        ];

        if ($genreId !== null) {
            $options['with_genres'] = $genreId;
        }

        $response = $this->request('/discover/movie', $options);

        if (!array_key_exists('results', $response)) {
            throw new \LogicException('The response from The Movie DB is invalid');
        }

        return MovieCollectionDTO::fromArray($response);
    }

    public function getMovie(int $movieId): MovieDTO
    {
        $response = $this->request("/movie/{$movieId}", [
            'append_to_response' => 'videos,credits,images,keywords,translations,production_companies',
        ]);

        if (empty($response)) {
            throw new NotFoundHttpException("Movie not found with ID: {$movieId}");
        }

        return MovieDTO::fromArray($response);
    }

    public function searchMovie(string $query): MovieCollectionDTO
    {
        $response = $this->request('/search/movie', [
            'query' => $query,
            'page' => 1,
            'region' => 'FR',
            'sort_by' => 'vote_average.desc',
        ]);

        if (!array_key_exists('results', $response)) {
            throw new \LogicException('The response from The Movie DB is invalid');
        }

        return MovieCollectionDTO::fromArray($response);
    }
}
