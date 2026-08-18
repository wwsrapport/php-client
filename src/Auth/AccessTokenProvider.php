<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Auth;

interface AccessTokenProvider
{
    public function token(): string;
}
