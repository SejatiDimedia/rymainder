<?php

namespace App\Domain\Communication\DataTransferObjects;

class DeliveryResult
{
    public function __construct(
        public readonly bool $isSuccess,
        public readonly ?string $messageId = null,
        public readonly ?string $errorMessage = null,
        public readonly bool $isSkipped = false,
    ) {
    }

    public static function success(?string $messageId = null): self
    {
        return new self(
            isSuccess: true,
            messageId: $messageId,
            errorMessage: null,
            isSkipped: false,
        );
    }

    public static function failure(string $errorMessage): self
    {
        return new self(
            isSuccess: false,
            messageId: null,
            errorMessage: $errorMessage,
            isSkipped: false,
        );
    }

    public static function skipped(string $reason): self
    {
        return new self(
            isSuccess: false,
            messageId: null,
            errorMessage: $reason,
            isSkipped: true,
        );
    }
}
