<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class PropertyPrefill extends ApiObject
{
    public function bagId(): ?string
    {
        return $this->string('bag_id');
    }

    public function energyLabel(): ?string
    {
        return $this->string('energy_label');
    }

    public function livingAreaM2(): ?float
    {
        return $this->float('living_area_m2');
    }

    public function wozValue(): ?int
    {
        return $this->int('woz_value');
    }
}
