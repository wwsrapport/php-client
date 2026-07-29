<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Model;

final class ReportValidationResult extends ApiObject
{
    public function valid(): ?bool
    {
        return $this->bool('valid');
    }

    /**
     * @return array<string, mixed>
     */
    public function errors(): array
    {
        return $this->array('errors');
    }

    /**
     * @return array<string, mixed>
     */
    public function warnings(): array
    {
        return $this->array('warnings');
    }
}
