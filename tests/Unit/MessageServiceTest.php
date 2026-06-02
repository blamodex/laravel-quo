<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Data\MessageData;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class MessageServiceTest extends TestCase
{
    private QuoService $quo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quo = app(QuoService::class);
    }

    public function test_list_messages(): void
    {
        Http::fake([
            'api.openphone.com/v1/messages*' => Http::response([
                'data' => [$this->messagePayload()],
                'nextPageToken' => 'page2',
            ]),
        ]);

        $result = $this->quo->messages()->list('PN123', ['+15551234567']);

        $this->assertCount(1, $result['messages']);
        $this->assertInstanceOf(MessageData::class, $result['messages'][0]);
        $this->assertSame('MSG1', $result['messages'][0]->id);
        $this->assertSame('page2', $result['nextPageToken']);
    }

    public function test_find_message(): void
    {
        Http::fake([
            'api.openphone.com/v1/messages/MSG1' => Http::response([
                'data' => $this->messagePayload(),
            ]),
        ]);

        $message = $this->quo->messages()->find('MSG1');

        $this->assertInstanceOf(MessageData::class, $message);
        $this->assertSame('MSG1', $message->id);
        $this->assertSame('Hello!', $message->text);
        $this->assertSame('outgoing', $message->direction);
    }

    public function test_send_message(): void
    {
        Http::fake([
            'api.openphone.com/v1/messages' => Http::response([
                'data' => $this->messagePayload(),
            ], 202),
        ]);

        $message = $this->quo->messages()->send('Hello!', '+15551234567', ['+15559876543']);

        $this->assertInstanceOf(MessageData::class, $message);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request['content'] === 'Hello!'
                && $request['from'] === '+15551234567';
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function messagePayload(): array
    {
        return [
            'id' => 'MSG1',
            'to' => ['+15559876543'],
            'from' => '+15551234567',
            'text' => 'Hello!',
            'phoneNumberId' => 'PN123',
            'direction' => 'outgoing',
            'userId' => 'USR1',
            'status' => 'delivered',
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:00:00Z',
        ];
    }
}
