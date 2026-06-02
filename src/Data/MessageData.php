<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class MessageData
{
    /**
     * @param array<string> $to
     */
    public function __construct(
        public string $id,
        public array $to,
        public string $from,
        public string $text,
        public ?string $phoneNumberId,
        public string $direction,
        public ?string $userId,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            to: $data['to'] ?? [],
            from: $data['from'],
            text: $data['text'],
            phoneNumberId: $data['phoneNumberId'] ?? null,
            direction: $data['direction'],
            userId: $data['userId'] ?? null,
            status: $data['status'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
        );
    }
}
