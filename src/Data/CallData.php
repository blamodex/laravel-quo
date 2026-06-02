<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class CallData
{
    /**
     * @param array<string> $participants
     */
    public function __construct(
        public string $id,
        public string $phoneNumberId,
        public ?string $userId,
        public string $direction,
        public string $status,
        public string $createdAt,
        public ?string $updatedAt,
        public ?string $answeredAt,
        public ?string $completedAt,
        public ?string $answeredBy,
        public ?string $initiatedBy,
        public ?int $duration,
        public array $participants,
        public ?string $callRoute,
        public ?string $forwardedFrom,
        public ?string $forwardedTo,
        public ?string $aiHandled,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            phoneNumberId: $data['phoneNumberId'],
            userId: $data['userId'] ?? null,
            direction: $data['direction'],
            status: $data['status'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'] ?? null,
            answeredAt: $data['answeredAt'] ?? null,
            completedAt: $data['completedAt'] ?? null,
            answeredBy: $data['answeredBy'] ?? null,
            initiatedBy: $data['initiatedBy'] ?? null,
            duration: isset($data['duration']) ? (int) $data['duration'] : null,
            participants: $data['participants'] ?? [],
            callRoute: $data['callRoute'] ?? null,
            forwardedFrom: $data['forwardedFrom'] ?? null,
            forwardedTo: $data['forwardedTo'] ?? null,
            aiHandled: $data['aiHandled'] ?? null,
        );
    }
}
