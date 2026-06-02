<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Data\PhoneNumberData;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class PhoneNumberServiceTest extends TestCase
{
    private QuoService $quo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quo = app(QuoService::class);
    }

    public function test_list_phone_numbers(): void
    {
        Http::fake([
            'api.openphone.com/v1/phone-numbers*' => Http::response([
                'data' => [$this->phoneNumberPayload()],
            ]),
        ]);

        $numbers = $this->quo->phoneNumbers()->list();

        $this->assertCount(1, $numbers);
        $this->assertInstanceOf(PhoneNumberData::class, $numbers[0]);
        $this->assertSame('PN123', $numbers[0]->id);
        $this->assertSame('+15551234567', $numbers[0]->number);
    }

    public function test_find_phone_number(): void
    {
        Http::fake([
            'api.openphone.com/v1/phone-numbers/PN123' => Http::response([
                'data' => $this->phoneNumberPayload(),
            ]),
        ]);

        $number = $this->quo->phoneNumbers()->find('PN123');

        $this->assertInstanceOf(PhoneNumberData::class, $number);
        $this->assertSame('PN123', $number->id);
        $this->assertSame('Main Line', $number->name);
        $this->assertSame('GRP1', $number->groupId);
    }

    /**
     * @return array<string, mixed>
     */
    private function phoneNumberPayload(): array
    {
        return [
            'id' => 'PN123',
            'groupId' => 'GRP1',
            'name' => 'Main Line',
            'number' => '+15551234567',
            'portRequestId' => null,
            'formattedNumber' => '(555) 123-4567',
            'forward' => null,
            'portingStatus' => null,
            'symbol' => null,
            'users' => [],
            'restrictions' => null,
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:00:00Z',
        ];
    }
}
