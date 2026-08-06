<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use InvalidArgumentException;
use Wwsrapport\Client\Http\ApiClient;

final class RegistryResource
{
    public function __construct(private readonly ApiClient $api) {}

    public function deriveBagReference(string $bagVboId): array
    {
        $this->validate($bagVboId);
        return $this->api->json('POST', '/registry/bag-reference', ['bagVboId' => $bagVboId]);
    }

    public function searchByBag(string $bagVboId): array
    {
        $this->validate($bagVboId);
        return $this->api->json('POST', '/registry/search-by-bag', ['bagVboId' => $bagVboId]);
    }

    private function validate(string $bagVboId): void
    {
        if (! preg_match('/^[0-9]{16}$/D', $bagVboId)) {
            throw new InvalidArgumentException('BAG verblijfsobject ID must contain exactly sixteen digits.');
        }
    }
}
