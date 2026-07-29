<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Exception;

final class ValidationException extends ApiException
{
    /**
     * @return array<string, mixed>
     */
    public function errors(): array
    {
        $problem = $this->problem();

        return is_array($problem['errors'] ?? null) ? $problem['errors'] : [];
    }
}
