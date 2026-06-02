<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class ConversationData
{
    /**
     * @param array<string> $participants
     */
    public function __construct(
        public string $id,
        public string $phoneNumberId,
        public array $participants,
        public ?string $name,
        public ?string $assignedTo,
        public ?string $createdAt,
        public ?string $updatedAt,
        public ?string $deletedAt,
        public ?string $lastActivityAt,
        public ?string $lastActivityId,
        public ?string $mutedUntil,
        public ?string $snoozedUntil,
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
            participants: $data['participants'] ?? [],
            name: $data['name'] ?? null,
            assignedTo: $data['assignedTo'] ?? null,
            createdAt: $data['createdAt'] ?? null,
            updatedAt: $data['updatedAt'] ?? null,
            deletedAt: $data['deletedAt'] ?? null,
            lastActivityAt: $data['lastActivityAt'] ?? null,
            lastActivityId: $data['lastActivityId'] ?? null,
            mutedUntil: $data['mutedUntil'] ?? null,
            snoozedUntil: $data['snoozedUntil'] ?? null,
        );
    }
}
