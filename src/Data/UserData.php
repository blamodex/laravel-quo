<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class UserData
{
    public function __construct(
        public string $id,
        public string $email,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $pictureUrl,
        public string $role,
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
            email: $data['email'],
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            pictureUrl: $data['pictureUrl'] ?? null,
            role: $data['role'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt'],
        );
    }
}
