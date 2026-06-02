<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class PhoneNumberData
{
    /**
     * @param array<mixed> $users
     * @param array<string, mixed>|null $restrictions
     */
    public function __construct(
        public string $id,
        public string $groupId,
        public string $name,
        public string $number,
        public ?string $portRequestId,
        public ?string $formattedNumber,
        public ?string $forward,
        public ?string $portingStatus,
        public ?string $symbol,
        public array $users,
        public ?array $restrictions,
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
            groupId: $data['groupId'],
            name: $data['name'],
            number: $data['number'],
            portRequestId: $data['portRequestId'] ?? null,
            formattedNumber: $data['formattedNumber'] ?? null,
            forward: $data['forward'] ?? null,
            portingStatus: $data['portingStatus'] ?? null,
            symbol: $data['symbol'] ?? null,
            users: $data['users'] ?? [],
            restrictions: $data['restrictions'] ?? null,
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
        );
    }
}
