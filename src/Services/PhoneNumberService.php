<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

use Blamodex\Quo\Data\PhoneNumberData;
use Blamodex\Quo\Services\Concerns\MakesRequests;

class PhoneNumberService
{
    use MakesRequests;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return array<PhoneNumberData>
     */
    public function list(?string $userId = null): array
    {
        $query = array_filter([
            'userId' => $userId,
        ], fn($value) => $value !== null);

        $data = $this->get('/v1/phone-numbers', $query);

        return array_map(
            fn(array $phoneNumber) => PhoneNumberData::fromArray($phoneNumber),
            $data['data'] ?? [],
        );
    }

    public function find(string $phoneNumberId): PhoneNumberData
    {
        $data = $this->get('/v1/phone-numbers/' . rawurlencode($phoneNumberId));

        return PhoneNumberData::fromArray($data['data']);
    }
}
