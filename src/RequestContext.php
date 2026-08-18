<?php

declare(strict_types=1);

namespace Wwsrapport\Client;

final class RequestContext
{
    public function __construct(
        public readonly ?string $municipalityCode = null,
        public readonly ?string $purposeCode = null,
        public readonly ?string $caseReference = null,
        public readonly ?string $clientReference = null,
    ) {}

    /** @return array<string,string> */
    public function headers(): array
    {
        return array_filter([
            'X-WWS-Municipality-Code' => $this->municipalityCode,
            'X-WWS-Purpose-Code' => $this->purposeCode,
            'X-WWS-Case-Reference' => $this->caseReference,
            'X-WWS-Client-Reference' => $this->clientReference,
        ], static fn (?string $value): bool => $value !== null && $value !== '');
    }
}
