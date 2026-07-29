<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;
use Wwsrapport\Client\Model\Ruleset;

final class RulesetsResource
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->api->json('GET', '/rulesets');
    }

    /**
     * @return array<int, Ruleset>
     */
    public function listObjects(): array
    {
        return array_map(
            static fn (array $item): Ruleset => Ruleset::fromArray($item),
            $this->dataList($this->list()),
        );
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
