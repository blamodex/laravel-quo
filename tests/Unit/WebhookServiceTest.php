<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Data\WebhookData;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class WebhookServiceTest extends TestCase
{
    private QuoService $quo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quo = app(QuoService::class);
    }

    public function test_list_webhooks(): void
    {
        Http::fake([
            'api.openphone.com/v1/webhooks*' => Http::response([
                'data' => [$this->webhookPayload()],
            ]),
        ]);

        $webhooks = $this->quo->webhooks()->list();

        $this->assertCount(1, $webhooks);
        $this->assertInstanceOf(WebhookData::class, $webhooks[0]);
        $this->assertSame('WH1', $webhooks[0]->id);
    }

    public function test_find_webhook(): void
    {
        Http::fake([
            'api.openphone.com/v1/webhooks/WH1' => Http::response([
                'data' => $this->webhookPayload(),
            ]),
        ]);

        $webhook = $this->quo->webhooks()->find('WH1');

        $this->assertInstanceOf(WebhookData::class, $webhook);
        $this->assertSame('WH1', $webhook->id);
        $this->assertSame('https://example.com/webhook', $webhook->url);
        $this->assertSame(['call.completed'], $webhook->events);
    }

    public function test_delete_webhook(): void
    {
        Http::fake([
            'api.openphone.com/v1/webhooks/WH1' => Http::response([], 204),
        ]);

        $this->quo->webhooks()->delete('WH1');

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE';
        });
    }

    public function test_create_for_calls(): void
    {
        Http::fake([
            'api.openphone.com/v1/webhooks/calls' => Http::response([
                'data' => $this->webhookPayload(),
            ], 201),
        ]);

        $webhook = $this->quo->webhooks()->createForCalls(
            'https://example.com/webhook',
            ['call.completed'],
        );

        $this->assertInstanceOf(WebhookData::class, $webhook);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request['url'] === 'https://example.com/webhook'
                && $request['events'] === ['call.completed'];
        });
    }

    public function test_create_for_messages(): void
    {
        Http::fake([
            'api.openphone.com/v1/webhooks/messages' => Http::response([
                'data' => $this->webhookPayload(),
            ], 201),
        ]);

        $webhook = $this->quo->webhooks()->createForMessages(
            'https://example.com/webhook',
            ['message.received'],
            ['label' => 'My Webhook'],
        );

        $this->assertInstanceOf(WebhookData::class, $webhook);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request['label'] === 'My Webhook';
        });
    }

    public function test_create_for_call_summaries(): void
    {
        Http::fake([
            'api.openphone.com/v1/webhooks/call-summaries' => Http::response([
                'data' => $this->webhookPayload(),
            ], 201),
        ]);

        $webhook = $this->quo->webhooks()->createForCallSummaries(
            'https://example.com/webhook',
            ['call.summary.completed'],
        );

        $this->assertInstanceOf(WebhookData::class, $webhook);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), 'call-summaries');
        });
    }

    public function test_create_for_call_transcripts(): void
    {
        Http::fake([
            'api.openphone.com/v1/webhooks/call-transcripts' => Http::response([
                'data' => $this->webhookPayload(),
            ], 201),
        ]);

        $webhook = $this->quo->webhooks()->createForCallTranscripts(
            'https://example.com/webhook',
            ['call.transcript.completed'],
        );

        $this->assertInstanceOf(WebhookData::class, $webhook);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), 'call-transcripts');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function webhookPayload(): array
    {
        return [
            'id' => 'WH1',
            'userId' => 'USR1',
            'orgId' => 'ORG1',
            'label' => 'My Webhook',
            'status' => 'active',
            'url' => 'https://example.com/webhook',
            'key' => 'whk_abc123',
            'events' => ['call.completed'],
            'resourceIds' => null,
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:00:00Z',
            'deletedAt' => null,
        ];
    }
}
