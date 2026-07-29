<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Resources;

use Wwsrapport\Client\Http\ApiClient;
use Wwsrapport\Client\Model\WebhookDelivery;
use Wwsrapport\Client\Model\WebhookEndpoint;

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
     * @return array<int, WebhookEndpoint>
     */
    public function listObjects(array $query = []): array
    {
        return array_map(
            static fn (array $item): WebhookEndpoint => WebhookEndpoint::fromArray($item),
            $this->dataList($this->list($query)),
        );
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
     * @param  array<int, string>  $events
     */
    public function createObject(string $url, array $events = [], ?string $description = null): WebhookEndpoint
    {
        return WebhookEndpoint::fromArray($this->data($this->create($url, $events, $description)));
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $webhookId): array
    {
        return $this->api->json('GET', '/webhooks/'.$this->encode($webhookId));
    }

    public function getObject(string $webhookId): WebhookEndpoint
    {
        return WebhookEndpoint::fromArray($this->data($this->get($webhookId)));
    }

    /**
     * @return array<string, mixed>
     */
    public function update(string $webhookId, array $payload): array
    {
        return $this->api->json('PATCH', '/webhooks/'.$this->encode($webhookId), $payload);
    }

    public function updateObject(string $webhookId, array $payload): WebhookEndpoint
    {
        return WebhookEndpoint::fromArray($this->data($this->update($webhookId, $payload)));
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

    public function sendTestObject(string $webhookId, string $eventType = 'report.completed'): WebhookDelivery
    {
        return WebhookDelivery::fromArray($this->data($this->sendTest($webhookId, $eventType)));
    }

    /**
     * @return array<string, mixed>
     */
    public function deliveries(string $webhookId, array $query = []): array
    {
        return $this->api->json('GET', '/webhooks/'.$this->encode($webhookId).'/deliveries', null, $query);
    }

    /**
     * @return array<int, WebhookDelivery>
     */
    public function deliveryObjects(string $webhookId, array $query = []): array
    {
        return array_map(
            static fn (array $item): WebhookDelivery => WebhookDelivery::fromArray($item),
            $this->dataList($this->deliveries($webhookId, $query)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function retryDelivery(string $webhookId, string $deliveryId): array
    {
        return $this->api->json('POST', '/webhooks/'.$this->encode($webhookId).'/deliveries/'.$this->encode($deliveryId).'/retry');
    }

    public function retryDeliveryObject(string $webhookId, string $deliveryId): WebhookDelivery
    {
        return WebhookDelivery::fromArray($this->data($this->retryDelivery($webhookId, $deliveryId)));
    }

    private function encode(string $value): string
    {
        return rawurlencode($value);
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
