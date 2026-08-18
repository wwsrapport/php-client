<?php

declare(strict_types=1);

namespace Wwsrapport\Client;

final class Config
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $baseUrl = 'https://wwsrapport.nl/v1',
        public readonly string $userAgent = 'wwsrapport-php-client/0.3.0',
        public readonly ?RequestContext $requestContext = null,
        public readonly string $apiVersion = '1.2.0',
    ) {
        if ($this->apiKey === '') {
            throw new \InvalidArgumentException('The WWSrapport API key may not be empty.');
        }

        if ($this->baseUrl === '') {
            throw new \InvalidArgumentException('The WWSrapport base URL may not be empty.');
        }
        if ($this->apiVersion === '') {
            throw new \InvalidArgumentException('The WWSrapport API version may not be empty.');
        }
    }

    public function normalizedBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    public function tokenUrl(): string
    {
        $parts = parse_url($this->normalizedBaseUrl());
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            throw new \InvalidArgumentException('The WWSrapport base URL must be an absolute URL.');
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').'/oauth/token';
    }
}
