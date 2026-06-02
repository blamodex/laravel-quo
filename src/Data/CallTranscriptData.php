<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class CallTranscriptData
{
    /**
     * @param array<DialogueSegmentData>|null $dialogue
     */
    public function __construct(
        public string $callId,
        public string $status,
        public string $createdAt,
        public ?float $duration,
        public ?array $dialogue,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $dialogue = null;

        if (isset($data['dialogue']) && is_array($data['dialogue'])) {
            $dialogue = array_map(
                fn(array $segment) => DialogueSegmentData::fromArray($segment),
                $data['dialogue'],
            );
        }

        return new self(
            callId: $data['callId'],
            status: $data['status'],
            createdAt: $data['createdAt'],
            duration: isset($data['duration']) ? (float) $data['duration'] : null,
            dialogue: $dialogue,
        );
    }
}
