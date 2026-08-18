<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;

final class BatchesResource
{
    public function __construct(private readonly ApiClient $api) {}
    public function create(array $payload, string $idempotencyKey): array { return $this->api->json('POST', '/batches', $payload, [], ['Idempotency-Key' => $idempotencyKey]); }
    public function get(string $id): array { return $this->api->json('GET', '/batches/'.rawurlencode($id)); }
    public function retry(string $id, string $key): array { return $this->api->json('POST', '/batches/'.rawurlencode($id).'/retry', null, [], ['Idempotency-Key' => $key]); }
}
