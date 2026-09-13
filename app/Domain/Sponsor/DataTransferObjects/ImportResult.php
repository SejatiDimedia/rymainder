<?php

namespace App\Domain\Sponsor\DataTransferObjects;

class ImportResult
{
    /**
     * @param int $importedCount
     * @param int $updatedCount
     * @param int $skippedCount
     * @param array<int, string> $errors Row-indexed error messages
     */
    public function __construct(
        public readonly int $importedCount = 0,
        public readonly int $updatedCount = 0,
        public readonly int $skippedCount = 0,
        public readonly array $errors = [],
    ) {
    }

    public function totalProcessed(): int
    {
        return $this->importedCount + $this->updatedCount + $this->skippedCount + count($this->errors);
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function isSuccess(): bool
    {
        return ($this->importedCount > 0 || $this->updatedCount > 0) && empty($this->errors);
    }
}
