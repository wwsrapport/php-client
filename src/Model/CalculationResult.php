<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class CalculationResult extends ApiObject
{
    public function points(): ?float
    {
        return $this->float('points');
    }

    public function maxRentEur(): ?float
    {
        return $this->float('max_rent_eur');
    }

    public function rentSegment(): ?string
    {
        return $this->string('rent_segment');
    }

    public function rulesetVersion(): ?string
    {
        return $this->string('ruleset_version');
    }
}
