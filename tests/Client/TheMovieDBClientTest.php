<?php

namespace App\Tests\Client;

use App\Client\TheMovieDBClient;
use App\DTO\GenreDTO;
use App\DTO\MovieCollectionDTO;
use App\DTO\MovieDTO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TheMovieDBClientTest extends TestCase
{
    private MockObject|HttpClientInterface $httpClient;
    private TheMovieDBClient $client;
    private MockObject|ResponseInterface $response;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->client = new TheMovieDBClient($this->httpClient);
    }

    public function testGetMovie(): void
    {
        $movieId = 123;
        $movieData = [
            'id' => $movieId,
            'title' => 'Test Movie',
            'overview' => 'Test Overview',
            'poster_path' => '/test.jpg',
            'vote_average' => 8.5,
            'vote_count' => 1000,
            'release_date' => '2024-01-01',
            'genres' => [],
            'videos' => ['results' => []],
            'credits' => []
        ];

        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($movieData));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/3/movie/'.$movieId,
                [
                    'query' => [
                        'append_to_response' => 'videos,credits,images,keywords,translations,production_companies'
                    ]
                ]
            )
            ->willReturn($this->response);

        $movie = $this->client->getMovie($movieId);

        $this->assertInstanceOf(MovieDTO::class, $movie);
        $this->assertEquals($movieId, $movie->id);
        $this->assertEquals('Test Movie', $movie->title);
        $this->assertEquals(1000, $movie->voteCount);
        $this->assertEquals(8.5, $movie->voteAverage);
    }

    public function testGetMovies(): void
    {
        $moviesData = [
            'page' => 1,
            'total_pages' => 10,
            'total_results' => 100,
            'results' => [
                [
                    'id' => 1,
                    'title' => 'Movie 1',
                    'overview' => 'Overview 1',
                    'poster_path' => '/test1.jpg',
                    'vote_average' => 8.5,
                    'vote_count' => 1000,
                    'release_date' => '2024-01-01'
                ]
            ]
        ];

        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($moviesData));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/3/discover/movie',
                [
                    'query' => [
                        'sort_by' => 'vote_average.desc',
                        'vote_count.gte' => 1000,
                        'vote_average.gte' => 7,
                        'page' => 1
                    ]
                ]
            )
            ->willReturn($this->response);

        $movies = $this->client->getMovies();

        $this->assertInstanceOf(MovieCollectionDTO::class, $movies);
        $this->assertEquals(1, $movies->page);
        $this->assertEquals(10, $movies->totalPages);
        $this->assertEquals(100, $movies->totalResults);
        $this->assertCount(1, $movies->results);
    }

    public function testSearchMovie(): void
    {
        $query = 'test';
        $searchData = [
            'page' => 1,
            'total_pages' => 1,
            'total_results' => 1,
            'results' => [
                [
                    'id' => 1,
                    'title' => 'Test Movie',
                    'overview' => 'Test Overview',
                    'poster_path' => '/test.jpg',
                    'vote_average' => 8.5,
                    'vote_count' => 1000,
                    'release_date' => '2024-01-01'
                ]
            ]
        ];

        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($searchData));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/3/search/movie',
                [
                    'query' => [
                        'query' => $query,
                        'page' => 1,
                        'region' => 'FR',
                        'sort_by' => 'vote_average.desc'
                    ]
                ]
            )
            ->willReturn($this->response);

        $result = $this->client->searchMovie($query);

        $this->assertInstanceOf(MovieCollectionDTO::class, $result);
        $this->assertCount(1, $result->results);
        $this->assertEquals('Test Movie', $result->results[0]->title);
    }

    public function testGetGenres(): void
    {
        $genresData = [
            'genres' => [
                [
                    'id' => 1,
                    'name' => 'Action'
                ],
                [
                    'id' => 2,
                    'name' => 'Comedy'
                ]
            ]
        ];

        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn(json_encode($genresData));

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                '/3/genre/movie/list',
                ['query' => []]
            )
            ->willReturn($this->response);

        $genres = $this->client->getGenres();

        $this->assertIsArray($genres);
        $this->assertCount(2, $genres);
        $this->assertInstanceOf(GenreDTO::class, $genres[0]);
        $this->assertEquals('Action', $genres[0]->name);
        $this->assertEquals(1, $genres[0]->id);
    }

    public function testGetMovieWithInvalidIdThrowsException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Movie not found with ID: 999999999');

        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn('[]');

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn($this->response);

        $this->client->getMovie(999999999);
    }
}
