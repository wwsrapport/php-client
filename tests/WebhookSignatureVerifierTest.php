<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Tests;

use PHPUnit\Framework\TestCase;
use Wwsrapport\Client\Exception\WwsrapportException;
use Wwsrapport\Client\Webhooks\WebhookEvent;
use Wwsrapport\Client\Webhooks\WebhookSignatureVerifier;

final class WebhookSignatureVerifierTest extends TestCase
{
    public function test_it_verifies_a_valid_webhook_signature(): void
    {
        $payload = json_encode([
            'id' => 'evt_test_123',
            'type' => 'report.completed',
            'created_at' => '2026-07-29T10:00:00Z',
            'environment' => 'sandbox',
            'data' => ['report_id' => 'rpt_test_123'],
        ], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $secret = 'whsec_test_secret';
        $signature = 'v1='.WebhookSignatureVerifier::signature($payload, $timestamp, $secret);

        $verifier = new WebhookSignatureVerifier();

        self::assertTrue($verifier->verify($payload, $signature, $timestamp, $secret));
        self::assertTrue($verifier->verifyFromHeaders($payload, [
            'WWS-Webhook-Signature' => $signature,
            'WWS-Webhook-Timestamp' => (string) $timestamp,
        ], $secret));

        $event = WebhookEvent::fromPayload($payload);
        self::assertSame('evt_test_123', $event->id);
        self::assertSame('report.completed', $event->type);
        self::assertSame('rpt_test_123', $event->data['report_id']);
    }

    public function test_it_rejects_invalid_or_expired_signatures(): void
    {
        $payload = '{"id":"evt_test_123"}';
        $timestamp = time();
        $secret = 'whsec_test_secret';
        $verifier = new WebhookSignatureVerifier();

        self::assertFalse($verifier->verify($payload, 'v1=invalid', $timestamp, $secret));
        self::assertFalse($verifier->verify($payload, 'v1='.WebhookSignatureVerifier::signature($payload, $timestamp - 3600, $secret), $timestamp - 3600, $secret));

        $this->expectException(WwsrapportException::class);
        $verifier->assertValidFromHeaders($payload, [
            'WWS-Webhook-Signature' => 'v1=invalid',
            'WWS-Webhook-Timestamp' => (string) $timestamp,
        ], $secret);
    }
}
