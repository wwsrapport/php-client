<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;

final class UsageResource
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return array<string, mixed>
     */
    public function current(): array
    {
        return $this->api->json('GET', '/usage/current');
    }

    /**
     * @return array<string, mixed>
     */
    public function history(?int $months = null): array
    {
        $query = $months === null ? [] : ['months' => $months];

        return $this->api->json('GET', '/usage/history', null, $query);
    }
}
