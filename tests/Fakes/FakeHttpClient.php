<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Tests\Fakes;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class FakeHttpClient implements ClientInterface
{
    /**
     * @var array<int, RequestInterface>
     */
    public array $requests = [];

    /**
     * @param  array<int, ResponseInterface>  $responses
     */
    public function __construct(private array $responses) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if ($this->responses === []) {
            throw new \RuntimeException('No fake HTTP response configured.');
        }

        return array_shift($this->responses);
    }
}
