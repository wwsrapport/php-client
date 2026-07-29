<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class Ruleset extends ApiObject
{
    public function id(): ?string
    {
        return $this->string('id');
    }

    public function version(): ?string
    {
        return $this->string('version');
    }

    public function label(): ?string
    {
        return $this->string('label');
    }

    public function status(): ?string
    {
        return $this->string('status');
    }
}
