<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

use Blamodex\Quo\Data\MessageData;
use Blamodex\Quo\Services\Concerns\MakesRequests;

class MessageService
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
     * @return array{messages: array<MessageData>, totalItems: int|null, nextPageToken: string|null}
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

        $data = $this->get('/v1/messages', $query);

        return [
            'messages' => array_map(
                fn(array $message) => MessageData::fromArray($message),
                $data['data'] ?? [],
            ),
            'totalItems' => $data['totalItems'] ?? null,
            'nextPageToken' => $data['nextPageToken'] ?? null,
        ];
    }

    public function find(string $id): MessageData
    {
        $data = $this->get('/v1/messages/' . rawurlencode($id));

        return MessageData::fromArray($data['data']);
    }

    /**
     * @param array<string> $to
     * @param array<string, mixed> $options
     */
    public function send(string $content, string $from, array $to, array $options = []): MessageData
    {
        $body = array_filter([
            'content' => $content,
            'from' => $from,
            'to' => $to,
            'phoneNumberId' => $options['phoneNumberId'] ?? null,
            'userId' => $options['userId'] ?? null,
            'setInboxStatus' => $options['setInboxStatus'] ?? null,
        ], fn($value) => $value !== null);

        $data = $this->post('/v1/messages', $body);

        return MessageData::fromArray($data['data']);
    }
}
