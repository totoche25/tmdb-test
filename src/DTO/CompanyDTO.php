<?php

declare(strict_types=1);

namespace App\DTO;

class CompanyDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $logoPath,
        public readonly string $originCountry,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            logoPath: $data['logo_path'] ?? null,
            originCountry: $data['origin_country']
        );
    }
}
