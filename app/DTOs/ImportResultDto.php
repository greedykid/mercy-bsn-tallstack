<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ImportResultDto
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        public int $created = 0,
        public int $updated = 0,
        public int $skipped = 0,
        public array $errors = [],
    ) {}

    public function totalProcessed(): int
    {
        return $this->created + $this->updated + $this->skipped;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
