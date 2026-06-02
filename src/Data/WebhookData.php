<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class WebhookData
{
    /**
     * @param array<string> $events
     * @param array<string> $resourceIds
     */
    public function __construct(
        public string $id,
        public string $userId,
        public string $orgId,
        public ?string $label,
        public string $status,
        public string $url,
        public string $key,
        public array $events,
        public array $resourceIds,
        public string $createdAt,
        public string $updatedAt,
        public ?string $deletedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            userId: $data['userId'],
            orgId: $data['orgId'],
            label: $data['label'] ?? null,
            status: $data['status'],
            url: $data['url'],
            key: $data['key'],
            events: $data['events'] ?? [],
            resourceIds: $data['resourceIds'] ?? [],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
            deletedAt: $data['deletedAt'] ?? null,
        );
    }
}
