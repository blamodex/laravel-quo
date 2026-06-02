<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

use Blamodex\Quo\Data\ConversationData;
use Blamodex\Quo\Services\Concerns\MakesRequests;

class ConversationService
{
    use MakesRequests;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param array<string, mixed> $options
     * @return array{conversations: array<ConversationData>, totalItems: int|null, nextPageToken: string|null}
     */
    public function list(array $options = []): array
    {
        $query = array_filter([
            'maxResults' => $options['maxResults'] ?? 10,
            'phoneNumbers' => $options['phoneNumbers'] ?? null,
            'userId' => $options['userId'] ?? null,
            'createdAfter' => $options['createdAfter'] ?? null,
            'createdBefore' => $options['createdBefore'] ?? null,
            'updatedAfter' => $options['updatedAfter'] ?? null,
            'updatedBefore' => $options['updatedBefore'] ?? null,
            'excludeInactive' => $options['excludeInactive'] ?? null,
            'pageToken' => $options['pageToken'] ?? null,
        ], fn($value) => $value !== null);

        $data = $this->get('/v1/conversations', $query);

        return [
            'conversations' => array_map(
                fn(array $conversation) => ConversationData::fromArray($conversation),
                $data['data'] ?? [],
            ),
            'totalItems' => $data['totalItems'] ?? null,
            'nextPageToken' => $data['nextPageToken'] ?? null,
        ];
    }
}
