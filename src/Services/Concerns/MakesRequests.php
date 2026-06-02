<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services\Concerns;

use Blamodex\Quo\Exceptions\QuoApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

trait MakesRequests
{
    private string $apiKey;
    private string $baseUrl;

    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    protected function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, query: $query);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function post(string $path, array $data = []): array
    {
        return $this->request('POST', $path, data: $data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function patch(string $path, array $data = []): array
    {
        return $this->request('PATCH', $path, data: $data);
    }

    protected function deleteRequest(string $path): void
    {
        $this->request('DELETE', $path);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function request(
        string $method,
        string $path,
        array $query = [],
        array $data = [],
    ): array {
        try {
            $pending = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->timeout(15);

            $response = match ($method) {
                'GET' => $pending->get($this->baseUrl . $path, $query),
                'POST' => $pending->post($this->baseUrl . $path, $data),
                'PATCH' => $pending->patch($this->baseUrl . $path, $data),
                'DELETE' => $pending->delete($this->baseUrl . $path),
                // @codeCoverageIgnoreStart
                default => throw new \InvalidArgumentException(
                    "Unsupported HTTP method: {$method}",
                ),
                // @codeCoverageIgnoreEnd
            };
        } catch (ConnectionException $e) {
            throw new QuoApiException(
                message: 'Failed to connect to Quo API: ' . $e->getMessage(),
                errorCode: null,
                statusCode: 0,
                docs: null,
                previous: $e,
            );
        }

        if ($response->failed()) {
            throw QuoApiException::fromResponse(
                $response->status(),
                $response->json() ?? [],
            );
        }

        return $response->json() ?? [];
    }
}
