<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Wwsrapport\Client\Config;
use Wwsrapport\Client\Exception\ValidationException;
use Wwsrapport\Client\Model\Document;
use Wwsrapport\Client\Model\Report;
use Wwsrapport\Client\Model\WebhookEndpoint;
use Wwsrapport\Client\Tests\Fakes\FakeHttpClient;
use Wwsrapport\Client\WwsrapportClient;

final class WwsrapportClientTest extends TestCase
{
    public function test_it_derives_a_bag_reference_through_the_registry_api(): void
    {
        $http = new FakeHttpClient([new Response(200, ['Content-Type' => 'application/json'], '{"data":{"bagReference":"reference"}}')]);
        $client = $this->client($http);
        $client->registry()->deriveBagReference('0123456789012345');

        self::assertSame('/v1/registry/bag-reference', $http->requests[0]->getUri()->getPath());
        self::assertSame(['bagVboId' => '0123456789012345'], json_decode((string) $http->requests[0]->getBody(), true));
    }

    public function test_it_creates_a_report_with_authorization_and_idempotency_key(): void
    {
        $http = new FakeHttpClient([
            new Response(201, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    'id' => 'rpt_test_123',
                    'status' => 'paid',
                ],
                'meta' => [
                    'environment' => 'sandbox',
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $client = $this->client($http);

        $result = $client->reports()->create([
            'external_reference' => 'partner-order-1',
            'address' => [
                'postcode' => '3905RB',
                'house_number' => 4,
            ],
        ], 'partner-order-1');

        self::assertSame('rpt_test_123', $result['data']['id']);
        self::assertCount(1, $http->requests);

        $request = $http->requests[0];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('/v1/reports', $request->getUri()->getPath());
        self::assertSame('Bearer wwsr_test_secret', $request->getHeaderLine('Authorization'));
        self::assertSame('partner-order-1', $request->getHeaderLine('Idempotency-Key'));
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));

        $payload = json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('4', $payload['address']['house_number']);
    }

    public function test_it_maps_validation_errors(): void
    {
        $http = new FakeHttpClient([
            new Response(422, [
                'Content-Type' => 'application/problem+json',
                'X-Request-Id' => 'req_test_123',
            ], json_encode([
                'title' => 'Validation failed',
                'code' => 'validation_failed',
                'errors' => [
                    'address.postcode' => ['The postcode is required.'],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $client = $this->client($http);

        try {
            $client->reports()->validate([]);
            self::fail('Expected a validation exception.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->statusCode());
            self::assertSame('req_test_123', $exception->requestId());
            self::assertArrayHasKey('address.postcode', $exception->errors());
        }
    }

    public function test_it_downloads_documents_as_binary_pdf_content(): void
    {
        $http = new FakeHttpClient([
            new Response(200, ['Content-Type' => 'application/pdf'], '%PDF-1.4 test'),
        ]);
        $client = $this->client($http);

        $content = $client->documents()->downloadWwsReport('rpt_test_123');

        self::assertSame('%PDF-1.4 test', $content);
        self::assertSame('/v1/reports/rpt_test_123/documents/wws-report', $http->requests[0]->getUri()->getPath());
    }

    public function test_it_creates_webhooks_and_returns_one_time_secret(): void
    {
        $http = new FakeHttpClient([
            new Response(201, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    'id' => 'wh_test_123',
                    'signing_secret' => 'whsec_test_secret',
                    'secret_hint' => 't_secret',
                    'status' => 'active',
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $client = $this->client($http);

        $result = $client->webhooks()->create(
            'https://partner.example.test/wws/webhooks',
            ['report.completed', 'report.failed'],
            'Partner endpoint',
        );

        self::assertSame('wh_test_123', $result['data']['id']);
        self::assertSame('/v1/webhooks', $http->requests[0]->getUri()->getPath());

        $payload = json_decode((string) $http->requests[0]->getBody(), true);
        self::assertSame(['report.completed', 'report.failed'], $payload['events']);
    }

    public function test_it_can_map_report_responses_to_typed_objects(): void
    {
        $http = new FakeHttpClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    'id' => 'rpt_test_123',
                    'public_id' => 'rpt_public_123',
                    'status' => 'completed',
                    'points' => 159,
                    'max_rent_eur' => 1042.71,
                    'rent_segment' => 'regulated',
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $client = $this->client($http);

        $report = $client->reports()->getObject('rpt_test_123');

        self::assertInstanceOf(Report::class, $report);
        self::assertSame('rpt_test_123', $report->id());
        self::assertSame('rpt_public_123', $report->publicId());
        self::assertSame('completed', $report->status());
        self::assertSame(159.0, $report->points());
        self::assertSame(1042.71, $report->maxRentEur());
        self::assertSame('regulated', $report->rentSegment());
    }

    public function test_it_can_map_document_lists_to_typed_objects(): void
    {
        $http = new FakeHttpClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    [
                        'id' => 'doc_wws',
                        'type' => 'wws_report',
                        'status' => 'ready',
                        'filename' => 'WWSR-2026-000001.pdf',
                        'download_url' => 'https://wwsrapport.nl/download/doc_wws',
                    ],
                    [
                        'id' => 'doc_advice',
                        'type' => 'improvement_advice',
                        'status' => 'ready',
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $client = $this->client($http);

        $documents = $client->documents()->listObjects('rpt_test_123');

        self::assertCount(2, $documents);
        self::assertInstanceOf(Document::class, $documents[0]);
        self::assertSame('doc_wws', $documents[0]->id());
        self::assertSame('wws_report', $documents[0]->type());
        self::assertSame('WWSR-2026-000001.pdf', $documents[0]->filename());
    }

    public function test_it_can_map_webhooks_to_typed_objects(): void
    {
        $http = new FakeHttpClient([
            new Response(201, ['Content-Type' => 'application/json'], json_encode([
                'data' => [
                    'id' => 'wh_test_123',
                    'url' => 'https://partner.example.test/wws/webhooks',
                    'status' => 'active',
                    'enabled' => true,
                    'events' => ['report.completed'],
                    'signing_secret' => 'whsec_test_secret',
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $client = $this->client($http);

        $webhook = $client->webhooks()->createObject(
            'https://partner.example.test/wws/webhooks',
            ['report.completed'],
        );

        self::assertInstanceOf(WebhookEndpoint::class, $webhook);
        self::assertSame('wh_test_123', $webhook->id());
        self::assertTrue($webhook->enabled());
        self::assertSame(['report.completed'], $webhook->events());
        self::assertSame('whsec_test_secret', $webhook->signingSecret());
    }

    private function client(FakeHttpClient $http): WwsrapportClient
    {
        $factory = new Psr17Factory();

        return new WwsrapportClient(
            new Config('wwsr_test_secret', 'https://wwsrapport.nl/v1'),
            $http,
            $factory,
            $factory,
        );
    }
}
