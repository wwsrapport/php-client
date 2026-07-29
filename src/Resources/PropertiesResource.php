<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;

final class PropertiesResource
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * Prefill available public property data for an address.
     *
     * Pass either ['postcode' => '3905RB', 'house_number' => '4'] or
     * ['address' => ['postcode' => '3905RB', 'house_number' => '4']].
     *
     * @return array<string, mixed>
     */
    public function prefill(array $address): array
    {
        $payload = array_key_exists('address', $address) ? $address : ['address' => $address];

        return $this->api->json('POST', '/properties/prefill', $payload);
    }
}
