<?php

declare(strict_types=1);

namespace Blamodex\Quo\Exceptions;

use RuntimeException;

class QuoApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $statusCode,
        public readonly string $docs,
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function fromResponse(int $statusCode, array $body): self
    {
        return new self(
            message: $body['message'] ?? 'Unknown API error',
            errorCode: $body['code'] ?? '',
            statusCode: $statusCode,
            docs: $body['docs'] ?? '',
        );
    }
}
