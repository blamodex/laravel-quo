<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

use Blamodex\Quo\Data\WebhookData;
use Blamodex\Quo\Services\Concerns\MakesRequests;

class WebhookService
{
    use MakesRequests;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return array<WebhookData>
     */
    public function list(?string $userId = null): array
    {
        $query = array_filter([
            'userId' => $userId,
        ], fn($value) => $value !== null);

        $data = $this->get('/v1/webhooks', $query);

        return array_map(
            fn(array $webhook) => WebhookData::fromArray($webhook),
            $data['data'] ?? [],
        );
    }

    public function find(string $id): WebhookData
    {
        $data = $this->get('/v1/webhooks/' . rawurlencode($id));

        return WebhookData::fromArray($data['data']);
    }

    public function delete(string $id): void
    {
        $this->deleteRequest('/v1/webhooks/' . rawurlencode($id));
    }

    /**
     * @param array<string> $events
     * @param array<string, mixed> $options
     */
    public function createForCalls(string $url, array $events, array $options = []): WebhookData
    {
        return $this->createWebhook('/v1/webhooks/calls', $url, $events, $options);
    }

    /**
     * @param array<string> $events
     * @param array<string, mixed> $options
     */
    public function createForMessages(string $url, array $events, array $options = []): WebhookData
    {
        return $this->createWebhook('/v1/webhooks/messages', $url, $events, $options);
    }

    /**
     * @param array<string> $events
     * @param array<string, mixed> $options
     */
    public function createForCallSummaries(string $url, array $events, array $options = []): WebhookData
    {
        return $this->createWebhook('/v1/webhooks/call-summaries', $url, $events, $options);
    }

    /**
     * @param array<string> $events
     * @param array<string, mixed> $options
     */
    public function createForCallTranscripts(string $url, array $events, array $options = []): WebhookData
    {
        return $this->createWebhook('/v1/webhooks/call-transcripts', $url, $events, $options);
    }

    /**
     * @param array<string> $events
     * @param array<string, mixed> $options
     */
    private function createWebhook(string $path, string $url, array $events, array $options = []): WebhookData
    {
        $body = array_filter([
            'url' => $url,
            'events' => $events,
            'userId' => $options['userId'] ?? null,
            'label' => $options['label'] ?? null,
            'status' => $options['status'] ?? null,
            'resourceIds' => $options['resourceIds'] ?? null,
        ], fn($value) => $value !== null);

        $data = $this->post($path, $body);

        return WebhookData::fromArray($data['data']);
    }
}
