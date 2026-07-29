<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class Report extends ApiObject
{
    public function id(): ?string
    {
        return $this->string('id');
    }

    public function publicId(): ?string
    {
        return $this->string('public_id');
    }

    public function reportNumber(): ?string
    {
        return $this->string('report_number');
    }

    public function status(): ?string
    {
        return $this->string('status');
    }

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
}
