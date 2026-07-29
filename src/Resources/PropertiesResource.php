<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;
use Wwsrapport\Client\Model\PropertyPrefill;

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

    public function prefillObject(array $address): PropertyPrefill
    {
        return PropertyPrefill::fromArray($this->data($this->prefill($address)));
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
}
