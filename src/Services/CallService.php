<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

use Blamodex\Quo\Data\CallData;
use Blamodex\Quo\Data\CallRecordingData;
use Blamodex\Quo\Data\CallSummaryData;
use Blamodex\Quo\Data\CallTranscriptData;
use Blamodex\Quo\Data\CallVoicemailData;
use Blamodex\Quo\Services\Concerns\MakesRequests;

class CallService
{
    use MakesRequests;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param array<string> $participants
     * @param array<string, mixed> $options
     * @return array{calls: array<CallData>, totalItems: int|null, nextPageToken: string|null}
     */
    public function list(string $phoneNumberId, array $participants, array $options = []): array
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
            'totalItems' => $data['totalItems'] ?? null,
            'nextPageToken' => $data['nextPageToken'] ?? null,
        ];
    }

    public function find(string $callId): CallData
    {
        $id = rawurlencode($callId);
        $data = $this->get("/v1/calls/{$id}");

        return CallData::fromArray($data['data']);
    }

    /**
     * @return array<CallRecordingData>
     */
    public function getRecordings(string $callId): array
    {
        $id = rawurlencode($callId);
        $data = $this->get("/v1/call-recordings/{$id}");

        return array_map(
            fn(array $recording) => CallRecordingData::fromArray($recording),
            $data['data'] ?? [],
        );
    }

    public function getSummary(string $callId): CallSummaryData
    {
        $id = rawurlencode($callId);
        $data = $this->get("/v1/call-summaries/{$id}");

        return CallSummaryData::fromArray($data['data']);
    }

    public function getTranscript(string $callId): CallTranscriptData
    {
        $id = rawurlencode($callId);
        $data = $this->get("/v1/call-transcripts/{$id}");

        return CallTranscriptData::fromArray($data['data']);
    }

    public function getVoicemail(string $callId): CallVoicemailData
    {
        $id = rawurlencode($callId);
        $data = $this->get("/v1/call-voicemails/{$id}");

        return CallVoicemailData::fromArray($data['data']);
    }
}
