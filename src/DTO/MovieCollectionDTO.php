<?php

declare(strict_types=1);

namespace App\DTO;

class MovieCollectionDTO
{
    /**
     * @param MovieDTO[] $results
     */
    public function __construct(
        public readonly array $results,
        public readonly int $page,
        public readonly int $totalPages,
        public readonly int $totalResults,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            results: array_map(
                static fn (array $movie) => MovieDTO::fromArray($movie),
                $data['results']
            ),
            page: $data['page'],
            totalPages: $data['total_pages'],
            totalResults: $data['total_results'],
        );
    }
}
