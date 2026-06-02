# Blamodex Laravel Quo

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blamodex/laravel-quo.svg?style=flat-square)](https://packagist.org/packages/blamodex/laravel-quo)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/blamodex/laravel-quo/ci.yml?label=tests&style=flat-square)](https://github.com/blamodex/laravel-quo/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/blamodex/laravel-quo.svg?style=flat-square)](https://packagist.org/packages/blamodex/laravel-quo)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg?style=flat-square)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg?style=flat-square)](https://www.php.net/)

A Laravel wrapper for the Quo (OpenPhone) public API v1. Provides typed, namespaced access to all API resources with full test coverage.

---

## Table of Contents

- [Features](#-features)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
  - [Calls](#calls)
  - [Contacts](#contacts)
  - [Conversations](#conversations)
  - [Messages](#messages)
  - [Phone Numbers](#phone-numbers)
  - [Users](#users)
  - [Webhooks](#webhooks)
- [Error Handling](#-error-handling)
- [Testing](#-testing)
- [Project Structure](#-project-structure)
- [Contributing](#-contributing)
- [License](#-license)

---

## Features

- Full coverage of the Quo (OpenPhone) API v1 (27 endpoints)
- Namespaced resource services (calls, contacts, messages, etc.)
- Typed DTOs for all API responses
- Built-in error handling with `QuoApiException`
- Connection timeout and failure handling
- Laravel service provider with auto-discovery

---

## Installation

Install the package with Composer:

```bash
composer require blamodex/laravel-quo
```

---

## Configuration

Set your API key in `.env`:

```env
QUO_API_KEY=your-api-key-here
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=blamodex-quo-config
```

This creates `config/quo.php` with the following options:

```php
return [
    'api_key' => env('QUO_API_KEY', ''),
    'base_url' => env('QUO_BASE_URL', 'https://api.openphone.com'),
];
```

---

## Usage

Inject `QuoService` or resolve it from the container:

```php
use Blamodex\Quo\Services\QuoService;

$quo = app(QuoService::class);
```

### Calls

```php
// List calls for a phone number
$result = $quo->calls()->list('PN123', ['+15551234567'], [
    'maxResults' => 25,
    'createdAfter' => '2025-01-01T00:00:00Z',
]);
// $result['calls'] => array of CallData
// $result['totalItems'] => int
// $result['nextPageToken'] => string|null

// Get a single call
$call = $quo->calls()->find('AC123');

// Get recordings for a call
$recordings = $quo->calls()->getRecordings('AC123');

// Get call summary
$summary = $quo->calls()->getSummary('AC123');

// Get call transcript
$transcript = $quo->calls()->getTranscript('AC123');

// Get voicemail for a call
$voicemail = $quo->calls()->getVoicemail('AC123');
```

### Contacts

```php
// List contacts
$result = $quo->contacts()->list(['maxResults' => 50]);

// Get a contact
$contact = $quo->contacts()->find('CT123');

// Create a contact
$contact = $quo->contacts()->create([
    'firstName' => 'John',
    'lastName' => 'Doe',
    'phoneNumbers' => [['name' => 'Mobile', 'value' => '+15551234567']],
]);

// Update a contact
$contact = $quo->contacts()->update('CT123', [
    'defaultFields' => ['firstName' => 'Jane'],
]);

// Delete a contact
$quo->contacts()->delete('CT123');

// Get custom fields
$fields = $quo->contacts()->getCustomFields();
```

### Conversations

```php
// List conversations
$result = $quo->conversations()->list([
    'phoneNumbers' => ['+15551234567'],
    'excludeInactive' => true,
    'maxResults' => 25,
]);
```

### Messages

```php
// List messages
$result = $quo->messages()->list('PN123', ['+15551234567']);

// Get a message
$message = $quo->messages()->find('MSG123');

// Send a message
$message = $quo->messages()->send(
    'Hello!',
    '+15551234567',
    ['+15559876543'],
);
```

### Phone Numbers

```php
// List all phone numbers
$numbers = $quo->phoneNumbers()->list();

// List phone numbers for a user
$numbers = $quo->phoneNumbers()->list('US123');

// Get a phone number
$number = $quo->phoneNumbers()->find('PN123');
```

### Users

```php
// List users
$result = $quo->users()->list(['maxResults' => 50]);

// Get a user
$user = $quo->users()->find('US123');
```

### Webhooks

```php
// List webhooks
$webhooks = $quo->webhooks()->list();

// Get a webhook
$webhook = $quo->webhooks()->find('WH123');

// Create webhooks for different resources
$webhook = $quo->webhooks()->createForCalls('https://example.com/hook', ['call.completed']);
$webhook = $quo->webhooks()->createForMessages('https://example.com/hook', ['message.received']);
$webhook = $quo->webhooks()->createForCallSummaries('https://example.com/hook', ['call.summary.completed']);
$webhook = $quo->webhooks()->createForCallTranscripts('https://example.com/hook', ['call.transcript.completed']);

// Delete a webhook
$quo->webhooks()->delete('WH123');
```

---

## Error Handling

All API errors throw `QuoApiException`:

```php
use Blamodex\Quo\Exceptions\QuoApiException;

try {
    $call = $quo->calls()->find('AC_invalid');
} catch (QuoApiException $e) {
    $e->getMessage();   // "Call not found"
    $e->statusCode;     // 404
    $e->errorCode;      // "0900404"
    $e->docs;           // "https://docs.openphone.com/..."
    $e->getPrevious();  // Original exception (for connection failures)
}
```

Connection failures (timeouts, DNS errors) are also wrapped in `QuoApiException` with `statusCode: 0`.

---

## Testing

This package uses [Orchestra Testbench](https://github.com/orchestral/testbench) and [PHPUnit](https://phpunit.de/).

Run tests:

```bash
composer test
```

Check code style:

```bash
composer lint
```

Run static analysis:

```bash
composer analyze
```

Check coverage (with Xdebug):

```bash
composer test:coverage
```

Run all QA checks:

```bash
composer qa
```

---

## Project Structure

```
src/
├── QuoServiceProvider.php
├── config/
│   └── quo.php
├── Services/
│   ├── QuoService.php
│   ├── CallService.php
│   ├── ContactService.php
│   ├── ConversationService.php
│   ├── MessageService.php
│   ├── PhoneNumberService.php
│   ├── UserService.php
│   ├── WebhookService.php
│   └── Concerns/
│       └── MakesRequests.php
├── Data/
│   ├── CallData.php
│   ├── CallRecordingData.php
│   ├── CallSummaryData.php
│   ├── CallTranscriptData.php
│   ├── CallVoicemailData.php
│   ├── ContactCustomFieldData.php
│   ├── ContactData.php
│   ├── ConversationData.php
│   ├── DialogueSegmentData.php
│   ├── MessageData.php
│   ├── PhoneNumberData.php
│   ├── UserData.php
│   └── WebhookData.php
└── Exceptions/
    └── QuoApiException.php

tests/
├── TestCase.php
└── Unit/
    ├── CallServiceTest.php
    ├── ContactServiceTest.php
    ├── ConversationServiceTest.php
    ├── MessageServiceTest.php
    ├── PhoneNumberServiceTest.php
    ├── QuoServiceTest.php
    ├── UserServiceTest.php
    └── WebhookServiceTest.php
```

---

## Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

---

## License

MIT. See the [LICENSE](LICENSE) file.

---

## Links

- [Report a Bug](https://github.com/blamodex/laravel-quo/issues)
- [Request a Feature](https://github.com/blamodex/laravel-quo/issues)
- [View Changelog](CHANGELOG.md)
- [Quo API Documentation](https://www.quo.com/docs/mdx/api-reference/introduction)
