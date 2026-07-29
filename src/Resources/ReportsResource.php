<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;

final class ReportsResource
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return array<string, mixed>
     */
    public function validate(array $payload): array
    {
        return $this->api->json('POST', '/reports/validate', $payload);
    }

    /**
     * Create one immutable paid API report. Always pass a stable idempotency key
     * per partner order so retries cannot consume duplicate quota.
     *
     * @return array<string, mixed>
     */
    public function create(array $payload, string $idempotencyKey): array
    {
        return $this->api->json('POST', '/reports', $payload, [], [
            'Idempotency-Key' => $idempotencyKey,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function list(array $query = []): array
    {
        return $this->api->json('GET', '/reports', null, $query);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $reportId): array
    {
        return $this->api->json('GET', '/reports/'.$this->encode($reportId));
    }

    /**
     * @return array<string, mixed>
     */
    public function calculation(string $reportId): array
    {
        return $this->api->json('GET', '/reports/'.$this->encode($reportId).'/calculation');
    }

    /**
     * @return array<string, mixed>
     */
    public function improvementAdvice(string $reportId): array
    {
        return $this->api->json('GET', '/reports/'.$this->encode($reportId).'/improvement-advice');
    }

    private function encode(string $value): string
    {
        return rawurlencode($value);
    }
}
