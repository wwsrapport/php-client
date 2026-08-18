<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;

final class TenantLifecycleResource
{
    public function __construct(private readonly ApiClient $api) {}
    public function requestExport(string $key): array { return $this->api->json('POST', '/exports', null, [], ['Idempotency-Key' => $key]); }
    public function getExport(string $id): array { return $this->api->json('GET', '/exports/'.rawurlencode($id)); }
    public function createExportDownloadUrl(string $id): array { return $this->api->json('POST', '/exports/'.rawurlencode($id).'/download-url'); }
    public function requestOffboarding(string $reference): array { return $this->api->json('POST', '/offboarding', ['confirmation' => 'REQUEST_OFFBOARDING', 'requested_by_reference' => $reference]); }
}
