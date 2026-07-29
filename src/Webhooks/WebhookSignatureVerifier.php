<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Webhooks;

use Wwsrapport\Client\Exception\WwsrapportException;

final class WebhookSignatureVerifier
{
    public function __construct(private readonly int $toleranceSeconds = 300) {}

    /**
     * @param  array<string, string|array<int, string>>  $headers
     */
    public function verifyFromHeaders(string $payload, array $headers, string $secret): bool
    {
        $normalized = $this->normalizeHeaders($headers);

        return $this->verify(
            $payload,
            $normalized['wws-webhook-signature'] ?? '',
            $normalized['wws-webhook-timestamp'] ?? '',
            $secret,
        );
    }

    public function assertValidFromHeaders(string $payload, array $headers, string $secret): void
    {
        if (! $this->verifyFromHeaders($payload, $headers, $secret)) {
            throw new WwsrapportException('The WWSrapport webhook signature is invalid.');
        }
    }

    public function verify(string $payload, string $signatureHeader, string|int $timestamp, string $secret): bool
    {
        if ($payload === '' || $signatureHeader === '' || $timestamp === '' || $secret === '') {
            return false;
        }

        $timestamp = (int) $timestamp;

        if ($this->toleranceSeconds > 0 && abs(time() - $timestamp) > $this->toleranceSeconds) {
            return false;
        }

        $expected = self::signature($payload, $timestamp, $secret);

        foreach ($this->extractSignatures($signatureHeader) as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }

    public static function signature(string $payload, string|int $timestamp, string $secret): string
    {
        return hash_hmac('sha256', (string) $timestamp.'.'.$payload, $secret);
    }

    /**
     * @return array<int, string>
     */
    private function extractSignatures(string $signatureHeader): array
    {
        $signatures = [];

        foreach (explode(',', $signatureHeader) as $part) {
            $part = trim($part);

            if (str_starts_with($part, 'v1=')) {
                $signatures[] = substr($part, 3);
            }
        }

        return $signatures;
    }

    /**
     * @param  array<string, string|array<int, string>>  $headers
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = is_array($value) ? implode(',', $value) : $value;
        }

        return $normalized;
    }
}
