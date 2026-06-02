<?php

declare(strict_types=1);

namespace Blamodex\Quo\Services;

class QuoService
{
    private ?CallService $calls = null;
    private ?ContactService $contacts = null;
    private ?ConversationService $conversations = null;
    private ?MessageService $messages = null;
    private ?PhoneNumberService $phoneNumbers = null;
    private ?UserService $users = null;
    private ?WebhookService $webhooks = null;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
    ) {
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('Quo API key is required. Set the QUO_API_KEY environment variable.');
        }
    }

    public function calls(): CallService
    {
        return $this->calls ??= new CallService($this->apiKey, $this->baseUrl);
    }

    public function contacts(): ContactService
    {
        return $this->contacts ??= new ContactService($this->apiKey, $this->baseUrl);
    }

    public function conversations(): ConversationService
    {
        return $this->conversations ??= new ConversationService($this->apiKey, $this->baseUrl);
    }

    public function messages(): MessageService
    {
        return $this->messages ??= new MessageService($this->apiKey, $this->baseUrl);
    }

    public function phoneNumbers(): PhoneNumberService
    {
        return $this->phoneNumbers ??= new PhoneNumberService($this->apiKey, $this->baseUrl);
    }

    public function users(): UserService
    {
        return $this->users ??= new UserService($this->apiKey, $this->baseUrl);
    }

    public function webhooks(): WebhookService
    {
        return $this->webhooks ??= new WebhookService($this->apiKey, $this->baseUrl);
    }
}
