<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Webhooks;

final class WebhookEvent
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $createdAt,
        public readonly string $environment,
        public readonly array $data,
        public readonly array $raw,
    ) {}

    public static function fromPayload(string $payload): self
    {
        $decoded = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new \InvalidArgumentException('The webhook payload must be a JSON object.');
        }

        return new self(
            (string) ($decoded['id'] ?? ''),
            (string) ($decoded['type'] ?? ''),
            (string) ($decoded['created_at'] ?? ''),
            (string) ($decoded['environment'] ?? ''),
            is_array($decoded['data'] ?? null) ? $decoded['data'] : [],
            $decoded,
        );
    }
}
