<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

use Blamodex\Quo\Data\UserData;
use Blamodex\Quo\Services\Concerns\MakesRequests;

class UserService
{
    use MakesRequests;

    public function __construct(string $apiKey, string $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param array<string, mixed> $options
     * @return array{users: array<UserData>, totalItems: int|null, nextPageToken: string|null}
     */
    public function list(array $options = []): array
    {
        $query = array_filter([
            'maxResults' => $options['maxResults'] ?? 10,
            'pageToken' => $options['pageToken'] ?? null,
        ], fn($value) => $value !== null);

        $data = $this->get('/v1/users', $query);

        return [
            'users' => array_map(
                fn(array $user) => UserData::fromArray($user),
                $data['data'] ?? [],
            ),
            'totalItems' => $data['totalItems'] ?? null,
            'nextPageToken' => $data['nextPageToken'] ?? null,
        ];
    }

    public function find(string $userId): UserData
    {
        $data = $this->get('/v1/users/' . rawurlencode($userId));

        return UserData::fromArray($data['data']);
    }
}
