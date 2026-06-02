<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

use Blamodex\Quo\Data\ContactCustomFieldData;
use Blamodex\Quo\Data\ContactData;
use Blamodex\Quo\Services\Concerns\MakesRequests;

class ContactService
{
    use MakesRequests;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param array<string, mixed> $options
     * @return array{contacts: array<ContactData>, totalItems: int|null, nextPageToken: string|null}
     */
    public function list(array $options = []): array
    {
        $query = array_filter([
            'maxResults' => $options['maxResults'] ?? 10,
            'externalIds' => $options['externalIds'] ?? null,
            'sources' => $options['sources'] ?? null,
            'pageToken' => $options['pageToken'] ?? null,
        ], fn($value) => $value !== null);

        $data = $this->get('/v1/contacts', $query);

        return [
            'contacts' => array_map(
                fn(array $contact) => ContactData::fromArray($contact),
                $data['data'] ?? [],
            ),
            'totalItems' => $data['totalItems'] ?? null,
            'nextPageToken' => $data['nextPageToken'] ?? null,
        ];
    }

    public function find(string $id): ContactData
    {
        $data = $this->get('/v1/contacts/' . rawurlencode($id));

        return ContactData::fromArray($data['data']);
    }

    /**
     * @param array<string, mixed> $defaultFields
     * @param array<string, mixed> $options
     */
    public function create(array $defaultFields, array $options = []): ContactData
    {
        $body = array_filter([
            'defaultFields' => $defaultFields,
            'customFields' => $options['customFields'] ?? null,
            'createdByUserId' => $options['createdByUserId'] ?? null,
            'source' => $options['source'] ?? null,
            'sourceUrl' => $options['sourceUrl'] ?? null,
            'externalId' => $options['externalId'] ?? null,
        ], fn($value) => $value !== null);

        $data = $this->post('/v1/contacts', $body);

        return ContactData::fromArray($data['data']);
    }

    /**
     * @param array<string, mixed> $fields
     */
    public function update(string $id, array $fields): ContactData
    {
        $data = $this->patch('/v1/contacts/' . rawurlencode($id), $fields);

        return ContactData::fromArray($data['data']);
    }

    public function delete(string $id): void
    {
        $this->deleteRequest('/v1/contacts/' . rawurlencode($id));
    }

    /**
     * @return array<ContactCustomFieldData>
     */
    public function getCustomFields(): array
    {
        $data = $this->get('/v1/contact-custom-fields');

        return array_map(
            fn(array $field) => ContactCustomFieldData::fromArray($field),
            $data['data'] ?? [],
        );
    }
}
