<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class ContactData
{
    /**
     * @param array<string, mixed> $defaultFields
     * @param array<mixed> $customFields
     */
    public function __construct(
        public string $id,
        public ?string $externalId,
        public ?string $source,
        public ?string $sourceUrl,
        public array $defaultFields,
        public array $customFields,
        public string $createdAt,
        public string $updatedAt,
        public ?string $createdByUserId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            externalId: $data['externalId'] ?? null,
            source: $data['source'] ?? null,
            sourceUrl: $data['sourceUrl'] ?? null,
            defaultFields: $data['defaultFields'] ?? [],
            customFields: $data['customFields'] ?? [],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
            createdByUserId: $data['createdByUserId'] ?? null,
        );
    }
}
