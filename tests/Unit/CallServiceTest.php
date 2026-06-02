<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Data\CallData;
use Blamodex\Quo\Data\CallRecordingData;
use Blamodex\Quo\Data\CallSummaryData;
use Blamodex\Quo\Data\CallTranscriptData;
use Blamodex\Quo\Data\CallVoicemailData;
use Blamodex\Quo\Data\DialogueSegmentData;
use Blamodex\Quo\Exceptions\QuoApiException;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class CallServiceTest extends TestCase
{
    private QuoService $quo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quo = app(QuoService::class);
    }

    public function test_list_calls(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls*' => Http::response([
                'data' => [$this->callPayload()],
                'totalItems' => 1,
                'nextPageToken' => 'next-page',
            ]),
        ]);

        $result = $this->quo->calls()->list('PN123', ['+15551234567']);

        $this->assertCount(1, $result['calls']);
        $this->assertInstanceOf(CallData::class, $result['calls'][0]);
        $this->assertSame('AC123', $result['calls'][0]->id);
        $this->assertSame(1, $result['totalItems']);
        $this->assertSame('next-page', $result['nextPageToken']);
    }

    public function test_find_call(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls/AC123' => Http::response([
                'data' => $this->callPayload(),
            ]),
        ]);

        $call = $this->quo->calls()->find('AC123');

        $this->assertInstanceOf(CallData::class, $call);
        $this->assertSame('AC123', $call->id);
        $this->assertSame('incoming', $call->direction);
        $this->assertSame('completed', $call->status);
        $this->assertSame(120, $call->duration);
        $this->assertNull($call->forwardedFrom);
    }

    public function test_get_recordings(): void
    {
        Http::fake([
            'api.openphone.com/v1/call-recordings/AC123' => Http::response([
                'data' => [[
                    'id' => 'REC1',
                    'status' => 'completed',
                    'duration' => 60,
                    'startTime' => '2025-01-01T00:00:00Z',
                    'type' => 'audio/mpeg',
                    'url' => 'https://example.com/recording.mp3',
                ]],
            ]),
        ]);

        $recordings = $this->quo->calls()->getRecordings('AC123');

        $this->assertCount(1, $recordings);
        $this->assertInstanceOf(CallRecordingData::class, $recordings[0]);
        $this->assertSame('REC1', $recordings[0]->id);
    }

    public function test_get_summary(): void
    {
        Http::fake([
            'api.openphone.com/v1/call-summaries/AC123' => Http::response([
                'data' => [
                    'callId' => 'AC123',
                    'status' => 'completed',
                    'summary' => ['Customer called about billing.'],
                    'nextSteps' => ['Follow up.'],
                    'jobs' => null,
                ],
            ]),
        ]);

        $summary = $this->quo->calls()->getSummary('AC123');

        $this->assertInstanceOf(CallSummaryData::class, $summary);
        $this->assertSame('AC123', $summary->callId);
        $this->assertSame('completed', $summary->status);
    }

    public function test_get_transcript(): void
    {
        Http::fake([
            'api.openphone.com/v1/call-transcripts/AC123' => Http::response([
                'data' => [
                    'callId' => 'AC123',
                    'status' => 'completed',
                    'createdAt' => '2025-01-01T00:00:00Z',
                    'duration' => 120.5,
                    'dialogue' => [[
                        'content' => 'Hello',
                        'start' => 0.0,
                        'end' => 1.5,
                        'identifier' => '+15551234567',
                        'userId' => 'USR1',
                    ]],
                ],
            ]),
        ]);

        $transcript = $this->quo->calls()->getTranscript('AC123');

        $this->assertInstanceOf(CallTranscriptData::class, $transcript);
        $this->assertCount(1, $transcript->dialogue);
        $this->assertInstanceOf(DialogueSegmentData::class, $transcript->dialogue[0]);
    }

    public function test_get_voicemail(): void
    {
        Http::fake([
            'api.openphone.com/v1/call-voicemails/AC123' => Http::response([
                'data' => [
                    'id' => 'VM1',
                    'status' => 'completed',
                    'duration' => 30,
                    'transcript' => 'Please call me back.',
                    'recordingUrl' => 'https://example.com/vm.mp3',
                ],
            ]),
        ]);

        $voicemail = $this->quo->calls()->getVoicemail('AC123');

        $this->assertInstanceOf(CallVoicemailData::class, $voicemail);
        $this->assertSame('VM1', $voicemail->id);
        $this->assertSame(30, $voicemail->duration);
        $this->assertSame('Please call me back.', $voicemail->transcript);
    }

    public function test_api_error_throws_exception(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls/INVALID' => Http::response([
                'message' => 'Call not found',
                'code' => '0900404',
                'status' => 404,
                'docs' => 'https://docs.openphone.com',
            ], 404),
        ]);

        $this->expectException(QuoApiException::class);
        $this->expectExceptionMessage('Call not found');

        $this->quo->calls()->find('INVALID');
    }

    public function test_connection_failure_throws_exception(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
        });

        try {
            $this->quo->calls()->find('AC123');
            $this->fail('Expected QuoApiException');
        } catch (QuoApiException $e) {
            $this->assertStringContainsString('Failed to connect', $e->getMessage());
            $this->assertSame(0, $e->statusCode);
        }
    }

    public function test_authorization_header_is_sent(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls/AC1' => Http::response([
                'data' => $this->callPayload(),
            ]),
        ]);

        $this->quo->calls()->find('AC1');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'test-api-key');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function callPayload(): array
    {
        return [
            'id' => 'AC123',
            'phoneNumberId' => 'PN123',
            'userId' => 'USR1',
            'direction' => 'incoming',
            'status' => 'completed',
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:02:00Z',
            'answeredAt' => '2025-01-01T00:00:05Z',
            'completedAt' => '2025-01-01T00:02:00Z',
            'answeredBy' => 'USR1',
            'initiatedBy' => null,
            'duration' => 120,
            'participants' => ['+15551234567'],
            'callRoute' => 'phone-number',
            'forwardedFrom' => null,
            'forwardedTo' => null,
            'aiHandled' => null,
        ];
    }
}
