<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Auth;

final class StaticAccessTokenProvider implements AccessTokenProvider
{
    public function __construct(private readonly string $value) {}

    public function token(): string
    {
        return $this->value;
    }
}
