<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class WebhookDelivery extends ApiObject
{
    public function id(): ?string
    {
        return $this->string('id');
    }

    public function eventType(): ?string
    {
        return $this->string('event_type');
    }

    public function status(): ?string
    {
        return $this->string('status');
    }

    public function attemptCount(): ?int
    {
        return $this->int('attempt_count');
    }
}
