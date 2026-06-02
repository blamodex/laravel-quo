<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Data\UserData;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class UserServiceTest extends TestCase
{
    private QuoService $quo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quo = app(QuoService::class);
    }

    public function test_list_users(): void
    {
        Http::fake([
            'api.openphone.com/v1/users*' => Http::response([
                'data' => [$this->userPayload()],
                'nextPageToken' => null,
            ]),
        ]);

        $result = $this->quo->users()->list();

        $this->assertCount(1, $result['users']);
        $this->assertInstanceOf(UserData::class, $result['users'][0]);
        $this->assertSame('USR1', $result['users'][0]->id);
        $this->assertNull($result['nextPageToken']);
    }

    public function test_find_user(): void
    {
        Http::fake([
            'api.openphone.com/v1/users/USR1' => Http::response([
                'data' => $this->userPayload(),
            ]),
        ]);

        $user = $this->quo->users()->find('USR1');

        $this->assertInstanceOf(UserData::class, $user);
        $this->assertSame('USR1', $user->id);
        $this->assertSame('john@example.com', $user->email);
        $this->assertSame('John', $user->firstName);
        $this->assertSame('admin', $user->role);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(): array
    {
        return [
            'id' => 'USR1',
            'email' => 'john@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'pictureUrl' => null,
            'role' => 'admin',
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:00:00Z',
        ];
    }
}
