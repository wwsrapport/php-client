<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;

final class WebhooksResource
{
    public function __construct(private readonly ApiClient $api) {}

    /**
     * @return array<string, mixed>
     */
    public function list(array $query = []): array
    {
        return $this->api->json('GET', '/webhooks', null, $query);
    }

    /**
     * @param  array<int, string>  $events
     * @return array<string, mixed>
     */
    public function create(string $url, array $events = [], ?string $description = null): array
    {
        return $this->api->json('POST', '/webhooks', array_filter([
            'url' => $url,
            'events' => $events,
            'description' => $description,
        ], static fn ($value) => $value !== null && $value !== []));
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $webhookId): array
    {
        return $this->api->json('GET', '/webhooks/'.$this->encode($webhookId));
    }

    /**
     * @return array<string, mixed>
     */
    public function update(string $webhookId, array $payload): array
    {
        return $this->api->json('PATCH', '/webhooks/'.$this->encode($webhookId), $payload);
    }

    public function delete(string $webhookId): bool
    {
        $this->api->json('DELETE', '/webhooks/'.$this->encode($webhookId));

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function sendTest(string $webhookId, string $eventType = 'report.completed'): array
    {
        return $this->api->json('POST', '/webhooks/'.$this->encode($webhookId).'/test', [
            'event_type' => $eventType,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function deliveries(string $webhookId, array $query = []): array
    {
        return $this->api->json('GET', '/webhooks/'.$this->encode($webhookId).'/deliveries', null, $query);
    }

    /**
     * @return array<string, mixed>
     */
    public function retryDelivery(string $webhookId, string $deliveryId): array
    {
        return $this->api->json('POST', '/webhooks/'.$this->encode($webhookId).'/deliveries/'.$this->encode($deliveryId).'/retry');
    }

    private function encode(string $value): string
    {
        return rawurlencode($value);
    }
}
