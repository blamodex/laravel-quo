<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Data\CallData;
use Blamodex\Quo\Data\CallRecordingData;
use Blamodex\Quo\Data\CallSummaryData;
use Blamodex\Quo\Data\CallTranscriptData;
use Blamodex\Quo\Data\DialogueSegmentData;
use Blamodex\Quo\Exceptions\QuoApiException;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class QuoServiceTest extends TestCase
{
    private QuoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(QuoService::class);
    }

    public function test_list_calls_returns_call_data(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls*' => Http::response([
                'data' => [
                    $this->callPayload(),
                ],
                'pageToken' => 'next-page',
            ]),
        ]);

        $result = $this->service->listCalls('PN123', ['+15551234567']);

        $this->assertCount(1, $result['calls']);
        $this->assertInstanceOf(CallData::class, $result['calls'][0]);
        $this->assertSame('AC123', $result['calls'][0]->id);
        $this->assertSame('next-page', $result['pageToken']);
    }

    public function test_list_calls_passes_options(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls*' => Http::response([
                'data' => [],
                'pageToken' => null,
            ]),
        ]);

        $this->service->listCalls('PN123', ['+15551234567'], [
            'maxResults' => 50,
            'userId' => 'USR1',
            'createdAfter' => '2025-01-01T00:00:00Z',
            'pageToken' => 'abc',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'maxResults=50')
                && str_contains($request->url(), 'userId=USR1');
        });
    }

    public function test_get_call_returns_call_data(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls/AC123' => Http::response([
                'data' => $this->callPayload(),
            ]),
        ]);

        $call = $this->service->getCall('AC123');

        $this->assertInstanceOf(CallData::class, $call);
        $this->assertSame('AC123', $call->id);
        $this->assertSame('incoming', $call->direction);
        $this->assertSame('completed', $call->status);
        $this->assertSame(120, $call->duration);
        $this->assertSame(['+15551234567'], $call->participants);
    }

    public function test_get_call_recordings_returns_array(): void
    {
        Http::fake([
            'api.openphone.com/v1/call-recordings/AC123' => Http::response([
                'data' => [
                    [
                        'id' => 'REC1',
                        'status' => 'completed',
                        'duration' => 60,
                        'startTime' => '2025-01-01T00:00:00Z',
                        'type' => 'audio/mpeg',
                        'url' => 'https://example.com/recording.mp3',
                    ],
                ],
            ]),
        ]);

        $recordings = $this->service->getCallRecordings('AC123');

        $this->assertCount(1, $recordings);
        $this->assertInstanceOf(CallRecordingData::class, $recordings[0]);
        $this->assertSame('REC1', $recordings[0]->id);
        $this->assertSame(60, $recordings[0]->duration);
        $this->assertSame('https://example.com/recording.mp3', $recordings[0]->url);
    }

    public function test_get_call_summary_returns_dto(): void
    {
        Http::fake([
            'api.openphone.com/v1/call-summaries/AC123' => Http::response([
                'data' => [
                    'callId' => 'AC123',
                    'status' => 'completed',
                    'summary' => ['Customer called about billing.'],
                    'nextSteps' => ['Follow up with invoice.'],
                    'jobs' => null,
                ],
            ]),
        ]);

        $summary = $this->service->getCallSummary('AC123');

        $this->assertInstanceOf(CallSummaryData::class, $summary);
        $this->assertSame('AC123', $summary->callId);
        $this->assertSame(['Customer called about billing.'], $summary->summary);
        $this->assertSame(['Follow up with invoice.'], $summary->nextSteps);
    }

    public function test_get_call_transcript_returns_dto_with_dialogue(): void
    {
        Http::fake([
            'api.openphone.com/v1/call-transcripts/AC123' => Http::response([
                'data' => [
                    'callId' => 'AC123',
                    'status' => 'completed',
                    'createdAt' => '2025-01-01T00:00:00Z',
                    'duration' => 120.5,
                    'dialogue' => [
                        [
                            'content' => 'Hello, how can I help?',
                            'start' => 0.0,
                            'end' => 2.5,
                            'identifier' => '+15551234567',
                            'userId' => 'USR1',
                        ],
                        [
                            'content' => 'I have a question about my account.',
                            'start' => 3.0,
                            'end' => 5.5,
                            'identifier' => '+15559876543',
                            'userId' => null,
                        ],
                    ],
                ],
            ]),
        ]);

        $transcript = $this->service->getCallTranscript('AC123');

        $this->assertInstanceOf(CallTranscriptData::class, $transcript);
        $this->assertSame('AC123', $transcript->callId);
        $this->assertSame(120.5, $transcript->duration);
        $this->assertCount(2, $transcript->dialogue);
        $this->assertInstanceOf(DialogueSegmentData::class, $transcript->dialogue[0]);
        $this->assertSame('Hello, how can I help?', $transcript->dialogue[0]->content);
        $this->assertSame('USR1', $transcript->dialogue[0]->userId);
    }

    public function test_api_error_throws_exception(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls/INVALID' => Http::response([
                'message' => 'Call not found',
                'code' => '0900404',
                'status' => 404,
                'title' => 'Not Found',
                'docs' => 'https://docs.openphone.com/errors/0900404',
            ], 404),
        ]);

        $this->expectException(QuoApiException::class);
        $this->expectExceptionMessage('Call not found');

        $this->service->getCall('INVALID');
    }

    public function test_api_exception_contains_error_details(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls/BAD' => Http::response([
                'message' => 'Bad request',
                'code' => '0900400',
                'status' => 400,
                'title' => 'Bad Request',
                'docs' => 'https://docs.openphone.com/errors/0900400',
            ], 400),
        ]);

        try {
            $this->service->getCall('BAD');
            $this->fail('Expected QuoApiException');
        } catch (QuoApiException $e) {
            $this->assertSame('0900400', $e->errorCode);
            $this->assertSame(400, $e->statusCode);
            $this->assertSame('https://docs.openphone.com/errors/0900400', $e->docs);
        }
    }

    public function test_authorization_header_is_sent(): void
    {
        Http::fake([
            'api.openphone.com/v1/calls/AC1' => Http::response([
                'data' => $this->callPayload(),
            ]),
        ]);

        $this->service->getCall('AC1');

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
            'aiHandled' => null,
        ];
    }
}
