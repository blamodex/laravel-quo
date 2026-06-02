<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class ContactCustomFieldData
{
    public function __construct(
        public string $name,
        public string $key,
        public string $type,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            key: $data['key'],
            type: $data['type'],
        );
    }
}
