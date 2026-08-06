<?php

declare(strict_types=1);

namespace Wwsrapport\Client;

final class Config
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $baseUrl = 'https://wwsrapport.nl/v1',
        public readonly string $userAgent = 'wwsrapport-php-client/0.2.0',
    ) {
        if ($this->apiKey === '') {
            throw new \InvalidArgumentException('The WWSrapport API key may not be empty.');
        }

        if ($this->baseUrl === '') {
            throw new \InvalidArgumentException('The WWSrapport base URL may not be empty.');
        }
    }

    public function normalizedBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }
}
