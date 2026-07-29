<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Exception;

class ApiException extends WwsrapportException
{
    /**
     * @param  array<string, mixed>|null  $problem
     */
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
        private readonly ?array $problem = null,
        private readonly string $responseBody = '',
        private readonly ?string $requestId = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function problem(): ?array
    {
        return $this->problem;
    }

    public function responseBody(): string
    {
        return $this->responseBody;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }
}
