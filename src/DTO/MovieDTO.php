<?php

declare(strict_types=1);

namespace App\DTO;

class MovieDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $overview,
        public readonly ?string $posterPath,
        public readonly ?string $backdropPath,
        public readonly float $voteAverage,
        public readonly int $voteCount,
        public readonly string $releaseDate,
        public readonly string $releaseYear,
        public readonly array $genres,
        public readonly ?array $videos = null,
        public readonly ?array $credits = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            title: $data['title'],
            overview: $data['overview'],
            posterPath: $data['poster_path'] ?? null,
            backdropPath: $data['backdrop_path'] ?? null,
            voteAverage: $data['vote_average'],
            voteCount: $data['vote_count'],
            releaseDate: $data['release_date'],
            releaseYear: date('Y', strtotime($data['release_date'])),
            genres: array_map(
                static fn (array $genre) => GenreDTO::fromArray($genre),
                $data['genres'] ?? []
            ),
            videos: $data['videos']['results'] ?? null,
            credits: $data['credits'] ?? null,
        );
    }
}
