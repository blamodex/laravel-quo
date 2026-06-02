<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Data\ConversationData;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ConversationServiceTest extends TestCase
{
    private QuoService $quo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quo = app(QuoService::class);
    }

    public function test_list_conversations(): void
    {
        Http::fake([
            'api.openphone.com/v1/conversations*' => Http::response([
                'data' => [[
                    'id' => 'CV1',
                    'phoneNumberId' => 'PN123',
                    'participants' => ['+15551234567'],
                    'name' => null,
                    'assignedTo' => 'USR1',
                    'createdAt' => '2025-01-01T00:00:00Z',
                    'updatedAt' => '2025-01-01T00:00:00Z',
                    'deletedAt' => null,
                    'lastActivityAt' => '2025-01-01T00:00:00Z',
                    'lastActivityId' => 'ACT1',
                    'mutedUntil' => null,
                    'snoozedUntil' => null,
                ]],
                'nextPageToken' => null,
            ]),
        ]);

        $result = $this->quo->conversations()->list();

        $this->assertCount(1, $result['conversations']);
        $this->assertInstanceOf(ConversationData::class, $result['conversations'][0]);
        $this->assertSame('CV1', $result['conversations'][0]->id);
        $this->assertSame(['+15551234567'], $result['conversations'][0]->participants);
        $this->assertNull($result['nextPageToken']);
    }
}
