<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

use Blamodex\Quo\Data\CallData;
use Blamodex\Quo\Data\CallRecordingData;
use Blamodex\Quo\Data\CallSummaryData;
use Blamodex\Quo\Data\CallTranscriptData;
use Blamodex\Quo\Exceptions\QuoApiException;
use Illuminate\Support\Facades\Http;

class QuoService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * @param array<string> $participants
     * @param array<string, mixed> $options
     * @return array{calls: array<CallData>, pageToken: string|null}
     */
    public function listCalls(string $phoneNumberId, array $participants, array $options = []): array
    {
        $query = array_filter([
            'phoneNumberId' => $phoneNumberId,
            'participants' => $participants,
            'maxResults' => $options['maxResults'] ?? 10,
            'userId' => $options['userId'] ?? null,
            'createdAfter' => $options['createdAfter'] ?? null,
            'createdBefore' => $options['createdBefore'] ?? null,
            'pageToken' => $options['pageToken'] ?? null,
        ], fn($value) => $value !== null);

        $data = $this->get('/v1/calls', $query);

        return [
            'calls' => array_map(
                fn(array $call) => CallData::fromArray($call),
                $data['data'] ?? [],
            ),
            'pageToken' => $data['pageToken'] ?? null,
        ];
    }

    public function getCall(string $callId): CallData
    {
        $data = $this->get("/v1/calls/{$callId}");

        return CallData::fromArray($data['data']);
    }

    /**
     * @return array<CallRecordingData>
     */
    public function getCallRecordings(string $callId): array
    {
        $data = $this->get("/v1/call-recordings/{$callId}");

        return array_map(
            fn(array $recording) => CallRecordingData::fromArray($recording),
            $data['data'] ?? [],
        );
    }

    public function getCallSummary(string $callId): CallSummaryData
    {
        $data = $this->get("/v1/call-summaries/{$callId}");

        return CallSummaryData::fromArray($data['data']);
    }

    public function getCallTranscript(string $callId): CallTranscriptData
    {
        $data = $this->get("/v1/call-transcripts/{$callId}");

        return CallTranscriptData::fromArray($data['data']);
    }

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])->get($this->baseUrl . $path, $query);

        if ($response->failed()) {
            throw QuoApiException::fromResponse(
                $response->status(),
                $response->json() ?? [],
            );
        }

        return $response->json() ?? [];
    }
}
