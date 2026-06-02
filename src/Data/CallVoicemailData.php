<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class CallVoicemailData
{
    public function __construct(
        public string $id,
        public string $status,
        public ?int $duration,
        public ?string $transcript,
        public ?string $recordingUrl,
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
            transcript: $data['transcript'] ?? null,
            recordingUrl: $data['recordingUrl'] ?? null,
        );
    }
}
