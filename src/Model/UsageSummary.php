<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class UsageSummary extends ApiObject
{
    public function reportsIncluded(): ?int
    {
        return $this->int('reports_included');
    }

    public function reportsUsed(): ?int
    {
        return $this->int('reports_used');
    }

    public function reportsRemaining(): ?int
    {
        return $this->int('reports_remaining');
    }

    public function paymentHold(): bool
    {
        return $this->bool('payment_hold') ?? false;
    }
}
