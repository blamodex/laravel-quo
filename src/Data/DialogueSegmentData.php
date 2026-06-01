<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class DialogueSegmentData
{
    public function __construct(
        public string $content,
        public float $start,
        public float $end,
        public ?string $identifier,
        public ?string $userId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'] ?? '',
            start: (float) ($data['start'] ?? 0),
            end: (float) ($data['end'] ?? 0),
            identifier: $data['identifier'] ?? null,
            userId: $data['userId'] ?? null,
        );
    }
}
