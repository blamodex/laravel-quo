<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Data\ContactCustomFieldData;
use Blamodex\Quo\Data\ContactData;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ContactServiceTest extends TestCase
{
    private QuoService $quo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quo = app(QuoService::class);
    }

    public function test_list_contacts(): void
    {
        Http::fake([
            'api.openphone.com/v1/contacts*' => Http::response([
                'data' => [$this->contactPayload()],
                'nextPageToken' => 'page2',
            ]),
        ]);

        $result = $this->quo->contacts()->list();

        $this->assertCount(1, $result['contacts']);
        $this->assertInstanceOf(ContactData::class, $result['contacts'][0]);
        $this->assertSame('CT1', $result['contacts'][0]->id);
        $this->assertSame('page2', $result['nextPageToken']);
    }

    public function test_find_contact(): void
    {
        Http::fake([
            'api.openphone.com/v1/contacts/CT1' => Http::response([
                'data' => $this->contactPayload(),
            ]),
        ]);

        $contact = $this->quo->contacts()->find('CT1');

        $this->assertInstanceOf(ContactData::class, $contact);
        $this->assertSame('CT1', $contact->id);
        $this->assertSame('2025-01-01T00:00:00Z', $contact->createdAt);
    }

    public function test_create_contact(): void
    {
        Http::fake([
            'api.openphone.com/v1/contacts' => Http::response([
                'data' => $this->contactPayload(),
            ], 201),
        ]);

        $contact = $this->quo->contacts()->create([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'phoneNumbers' => [['name' => 'Mobile', 'value' => '+15551234567']],
        ]);

        $this->assertInstanceOf(ContactData::class, $contact);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && $request['defaultFields']['firstName'] === 'John';
        });
    }

    public function test_update_contact(): void
    {
        Http::fake([
            'api.openphone.com/v1/contacts/CT1' => Http::response([
                'data' => $this->contactPayload(),
            ]),
        ]);

        $contact = $this->quo->contacts()->update('CT1', [
            'defaultFields' => ['firstName' => 'Jane'],
        ]);

        $this->assertInstanceOf(ContactData::class, $contact);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH';
        });
    }

    public function test_delete_contact(): void
    {
        Http::fake([
            'api.openphone.com/v1/contacts/CT1' => Http::response([], 204),
        ]);

        $this->quo->contacts()->delete('CT1');

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE';
        });
    }

    public function test_get_custom_fields(): void
    {
        Http::fake([
            'api.openphone.com/v1/contact-custom-fields' => Http::response([
                'data' => [[
                    'name' => 'Company Size',
                    'key' => 'company_size',
                    'type' => 'number',
                ]],
            ]),
        ]);

        $fields = $this->quo->contacts()->getCustomFields();

        $this->assertCount(1, $fields);
        $this->assertInstanceOf(ContactCustomFieldData::class, $fields[0]);
        $this->assertSame('Company Size', $fields[0]->name);
        $this->assertSame('company_size', $fields[0]->key);
    }

    /**
     * @return array<string, mixed>
     */
    private function contactPayload(): array
    {
        return [
            'id' => 'CT1',
            'externalId' => null,
            'source' => null,
            'sourceUrl' => null,
            'defaultFields' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'phoneNumbers' => [['name' => 'Mobile', 'value' => '+15551234567', 'id' => 'PH1']],
                'emails' => [],
                'company' => null,
                'role' => null,
            ],
            'customFields' => [],
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:00:00Z',
            'createdByUserId' => 'USR1',
        ];
    }
}
