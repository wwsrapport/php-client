<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class ImprovementAdvice extends ApiObject
{
    public function status(): ?string
    {
        return $this->string('status');
    }

    public function currentPoints(): ?float
    {
        return $this->float('current_points');
    }

    public function targetPoints(): ?float
    {
        return $this->float('target_points');
    }

    public function possiblePointGain(): ?float
    {
        return $this->float('possible_point_gain');
    }

    /**
     * @return array<string, mixed>
     */
    public function opportunities(): array
    {
        return $this->array('opportunities');
    }
}
