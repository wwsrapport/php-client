<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class WebhookEndpoint extends ApiObject
{
    public function id(): ?string
    {
        return $this->string('id');
    }

    public function url(): ?string
    {
        return $this->string('url');
    }

    public function status(): ?string
    {
        return $this->string('status');
    }

    public function enabled(): bool
    {
        return $this->bool('enabled') ?? true;
    }

    /**
     * @return array<int, string>
     */
    public function events(): array
    {
        return array_values(array_filter($this->array('events'), 'is_string'));
    }

    public function signingSecret(): ?string
    {
        return $this->string('signing_secret');
    }
}
