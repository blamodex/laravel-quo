<?php

declare(strict_types=1);

namespace Blamodex\Quo\Data;

readonly class CallSummaryData
{
    /**
     * @param array<string>|null $summary
     * @param array<string>|null $nextSteps
     * @param array<array<string, mixed>>|null $jobs
     */
    public function __construct(
        public string $callId,
        public string $status,
        public ?array $summary,
        public ?array $nextSteps,
        public ?array $jobs,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            callId: $data['callId'],
            status: $data['status'],
            summary: $data['summary'] ?? null,
            nextSteps: $data['nextSteps'] ?? null,
            jobs: $data['jobs'] ?? null,
        );
    }
}
