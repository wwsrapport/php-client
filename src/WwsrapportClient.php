<?php

declare(strict_types=1);

namespace Wwsrapport\Client;

use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Wwsrapport\Client\Http\ApiClient;
use Wwsrapport\Client\Resources\DocumentsResource;
use Wwsrapport\Client\Resources\PropertiesResource;
use Wwsrapport\Client\Resources\ReportsResource;
use Wwsrapport\Client\Resources\RulesetsResource;
use Wwsrapport\Client\Resources\UsageResource;
use Wwsrapport\Client\Resources\WebhooksResource;

final class WwsrapportClient
{
    private ApiClient $api;

    public function __construct(
        Config $config,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ) {
        $this->api = new ApiClient($config, $httpClient, $requestFactory, $streamFactory);
    }

    public static function create(string $apiKey, ?string $baseUrl = null): self
    {
        if (! class_exists(GuzzleClient::class) || ! class_exists(Psr17Factory::class)) {
            throw new \RuntimeException(
                'WwsrapportClient::create() requires guzzlehttp/guzzle and nyholm/psr7. '
                .'Install them or pass your own PSR-18 client and PSR-17 factories to the constructor.'
            );
        }

        $factory = new Psr17Factory();

        return new self(
            new Config($apiKey, $baseUrl ?? 'https://wwsrapport.nl/v1'),
            new GuzzleClient(),
            $factory,
            $factory,
        );
    }

    public function properties(): PropertiesResource
    {
        return new PropertiesResource($this->api);
    }

    public function reports(): ReportsResource
    {
        return new ReportsResource($this->api);
    }

    public function documents(): DocumentsResource
    {
        return new DocumentsResource($this->api);
    }

    public function usage(): UsageResource
    {
        return new UsageResource($this->api);
    }

    public function rulesets(): RulesetsResource
    {
        return new RulesetsResource($this->api);
    }

    public function webhooks(): WebhooksResource
    {
        return new WebhooksResource($this->api);
    }
}
