<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;
use Wwsrapport\Client\Model\UsageSummary;

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

    public function currentObject(): UsageSummary
    {
        return UsageSummary::fromArray($this->data($this->current()));
    }

    /**
     * @return array<string, mixed>
     */
    public function history(?int $months = null): array
    {
        $query = $months === null ? [] : ['months' => $months];

        return $this->api->json('GET', '/usage/history', null, $query);
    }

    /**
     * @return array<int, UsageSummary>
     */
    public function historyObjects(?int $months = null): array
    {
        return array_map(
            static fn (array $item): UsageSummary => UsageSummary::fromArray($item),
            $this->dataList($this->history($months)),
        );
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    private function data(array $response): array
    {
        $data = $response['data'] ?? $response;

        return is_array($data) ? $data : [];
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<int, array<string, mixed>>
     */
    private function dataList(array $response): array
    {
        $data = $response['data'] ?? $response;

        if (! is_array($data)) {
            return [];
        }

        return array_values(array_filter($data, 'is_array'));
    }
}
