<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class CallRecordingData
{
    public function __construct(
        public string $id,
        public string $status,
        public ?int $duration,
        public ?string $startTime,
        public ?string $type,
        public ?string $url,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            duration: isset($data['duration']) ? (int) $data['duration'] : null,
            startTime: $data['startTime'] ?? null,
            type: $data['type'] ?? null,
            url: $data['url'] ?? null,
        );
    }
}
