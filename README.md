# WWSrapport PHP Client

Official PHP SDK for the [WWSrapport API](https://wwsrapport.nl/api/docs).

Use this package to create WWS reports, retrieve immutable report data, download PDFs, manage webhook endpoints, and verify webhook signatures.

## Installation

```bash
composer require wwsrapport/php-client
```

The client uses PSR-18 and PSR-17. For a quick default setup, install Guzzle and Nyholm PSR-7:

```bash
composer require guzzlehttp/guzzle nyholm/psr7
```

## Quick Start

```php
<?php

use Wwsrapport\Client\WwsrapportClient;

$client = WwsrapportClient::create($_ENV['WWSRAPPORT_API_KEY']);

$prefill = $client->properties()->prefill([
    'postcode' => '3905RB',
    'house_number' => '4',
]);

$report = $client->reports()->create([
    'external_reference' => 'partner-order-10001',
    'address' => [
        'postcode' => '3905RB',
        'house_number' => '4',
    ],
    'input_values' => [
        'living_area' => 53,
        'energy_label' => 'E',
        'woz_value' => 290000,
    ],
], idempotencyKey: 'partner-order-10001');

$reportId = $report['data']['id'];

file_put_contents(
    __DIR__.'/wws-report.pdf',
    $client->documents()->downloadWwsReport($reportId)
);

file_put_contents(
    __DIR__.'/wws-improvement-advice.pdf',
    $client->documents()->downloadImprovementAdvice($reportId)
);
```

## Authentication

Create an API key in the WWSrapport account area. The SDK sends it as a Bearer token:

```php
$client = WwsrapportClient::create('wwsr_live_...');
```

Sandbox and production access are controlled by the API key that WWSrapport issues.

## Reports

```php
$client->reports()->validate($payload);

$client->reports()->create($payload, 'stable-partner-idempotency-key');

$client->reports()->get('rpt_...');
$client->reports()->list(['external_reference' => 'partner-order-10001']);
$client->reports()->calculation('rpt_...');
$client->reports()->improvementAdvice('rpt_...');
```

Always pass an `Idempotency-Key` when creating a report. Use a stable key from your own order or request id so retries cannot create duplicate reports or consume duplicate quota.

## Documents

```php
$documents = $client->documents()->list('rpt_...');

$pdf = $client->documents()->downloadWwsReport('rpt_...');
$advicePdf = $client->documents()->downloadImprovementAdvice('rpt_...');

$client->documents()->downloadToFile('rpt_...', 'wws_report', __DIR__.'/report.pdf');
$client->documents()->downloadToFile('rpt_...', 'improvement_advice', __DIR__.'/advice.pdf');
```

Heredownloads of existing documents do not create a new report and do not consume report quota.

## Usage and Rulesets

```php
$usage = $client->usage()->current();
$history = $client->usage()->history(months: 6);
$rulesets = $client->rulesets()->list();
```

## Webhooks

```php
$webhook = $client->webhooks()->create(
    url: 'https://partner.example.com/webhooks/wwsrapport',
    events: ['report.completed', 'report.failed'],
    description: 'Production WWSrapport endpoint',
);

// The signing secret is returned only once.
$signingSecret = $webhook['data']['signing_secret'];

$client->webhooks()->sendTest($webhook['data']['id'], 'report.completed');
$deliveries = $client->webhooks()->deliveries($webhook['data']['id']);
```

Supported event names include:

- `report.queued`
- `report.processing`
- `report.completed`
- `report.failed`
- `report.cancelled`
- `report.documents.ready`
- `report.document.failed`

## Verifying Webhooks

WWSrapport signs webhook bodies with HMAC-SHA256:

```php
<?php

use Wwsrapport\Client\Webhooks\WebhookEvent;
use Wwsrapport\Client\Webhooks\WebhookSignatureVerifier;

$payload = file_get_contents('php://input');
$headers = getallheaders();

$verifier = new WebhookSignatureVerifier();
$verifier->assertValidFromHeaders($payload, $headers, $_ENV['WWSRAPPORT_WEBHOOK_SECRET']);

$event = WebhookEvent::fromPayload($payload);

if ($event->type === 'report.completed') {
    $reportId = $event->data['report_id'] ?? null;
    // Update your local order.
}
```

Signature headers:

- `WWS-Webhook-Id`
- `WWS-Webhook-Timestamp`
- `WWS-Webhook-Signature`
- `WWS-Webhook-Attempt`
- `WWS-Webhook-Environment`

## Error Handling

The SDK maps API responses to typed exceptions:

```php
use Wwsrapport\Client\Exception\ApiException;
use Wwsrapport\Client\Exception\ValidationException;

try {
    $client->reports()->create($payload, $idempotencyKey);
} catch (ValidationException $exception) {
    var_dump($exception->errors());
} catch (ApiException $exception) {
    error_log($exception->statusCode().' '.$exception->getMessage());
    error_log('Request ID: '.$exception->requestId());
}
```

Exception classes:

- `AuthenticationException` for HTTP 401
- `PaymentRequiredException` for HTTP 402
- `AuthorizationException` for HTTP 403
- `NotFoundException` for HTTP 404
- `ConflictException` for HTTP 409
- `ValidationException` for HTTP 422
- `RateLimitException` for HTTP 429
- `ApiException` for other non-success API responses

## Custom HTTP Client

```php
use Wwsrapport\Client\Config;
use Wwsrapport\Client\WwsrapportClient;

$client = new WwsrapportClient(
    new Config(apiKey: 'wwsr_live_...', baseUrl: 'https://wwsrapport.nl/v1'),
    $psr18Client,
    $psr17RequestFactory,
    $psr17StreamFactory,
);
```

## Development

```bash
composer install
composer test
```
