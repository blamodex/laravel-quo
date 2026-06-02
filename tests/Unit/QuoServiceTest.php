<?php

declare(strict_types=1);

namespace Blamodex\Quo\Tests\Unit;

use Blamodex\Quo\Services\CallService;
use Blamodex\Quo\Services\ContactService;
use Blamodex\Quo\Services\ConversationService;
use Blamodex\Quo\Services\MessageService;
use Blamodex\Quo\Services\PhoneNumberService;
use Blamodex\Quo\Services\QuoService;
use Blamodex\Quo\Services\UserService;
use Blamodex\Quo\Services\WebhookService;
use Blamodex\Quo\Tests\TestCase;

class QuoServiceTest extends TestCase
{
    private QuoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(QuoService::class);
    }

    public function test_empty_api_key_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quo API key is required');

        new QuoService(apiKey: '', baseUrl: 'https://api.openphone.com');
    }

    public function test_calls_returns_call_service(): void
    {
        $this->assertInstanceOf(CallService::class, $this->service->calls());
    }

    public function test_contacts_returns_contact_service(): void
    {
        $this->assertInstanceOf(ContactService::class, $this->service->contacts());
    }

    public function test_conversations_returns_conversation_service(): void
    {
        $this->assertInstanceOf(ConversationService::class, $this->service->conversations());
    }

    public function test_messages_returns_message_service(): void
    {
        $this->assertInstanceOf(MessageService::class, $this->service->messages());
    }

    public function test_phone_numbers_returns_phone_number_service(): void
    {
        $this->assertInstanceOf(PhoneNumberService::class, $this->service->phoneNumbers());
    }

    public function test_users_returns_user_service(): void
    {
        $this->assertInstanceOf(UserService::class, $this->service->users());
    }

    public function test_webhooks_returns_webhook_service(): void
    {
        $this->assertInstanceOf(WebhookService::class, $this->service->webhooks());
    }

    public function test_services_are_lazily_cached(): void
    {
        $this->assertSame($this->service->calls(), $this->service->calls());
        $this->assertSame($this->service->contacts(), $this->service->contacts());
    }
}
