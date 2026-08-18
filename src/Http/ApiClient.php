<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Wwsrapport\Client\Config;
use Wwsrapport\Client\Auth\AccessTokenProvider;
use Wwsrapport\Client\Auth\StaticAccessTokenProvider;
use Wwsrapport\Client\Exception\ApiException;
use Wwsrapport\Client\Exception\AuthenticationException;
use Wwsrapport\Client\Exception\AuthorizationException;
use Wwsrapport\Client\Exception\ConflictException;
use Wwsrapport\Client\Exception\NotFoundException;
use Wwsrapport\Client\Exception\PaymentRequiredException;
use Wwsrapport\Client\Exception\RateLimitException;
use Wwsrapport\Client\Exception\ValidationException;
use Wwsrapport\Client\Exception\WwsrapportException;

final class ApiClient
{
    public function __construct(
        private readonly Config $config,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        ?AccessTokenProvider $accessTokenProvider = null,
    ) {
        $this->accessTokenProvider = $accessTokenProvider ?? new StaticAccessTokenProvider($config->apiKey);
    }

    private readonly AccessTokenProvider $accessTokenProvider;

    /**
     * @return array<string, mixed>
     */
    public function json(string $method, string $path, ?array $body = null, array $query = [], array $headers = []): array
    {
        $response = $this->send($method, $path, $body, $query, $headers + ['Accept' => 'application/json']);
        $raw = (string) $response->getBody();

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->throwApiException($response, $raw);
        }

        if (trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw new ApiException('The WWSrapport API returned invalid JSON.', $response->getStatusCode(), null, $raw, $this->requestId($response));
        }

        return $decoded;
    }

    public function binary(string $path, array $query = [], array $headers = []): string
    {
        $response = $this->send('GET', $path, null, $query, $headers + ['Accept' => 'application/pdf']);
        $raw = (string) $response->getBody();

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->throwApiException($response, $raw);
        }

        return $raw;
    }

    private function send(string $method, string $path, ?array $body, array $query, array $headers): ResponseInterface
    {
        $url = $this->url($path, $query);
        $requestId = 'req_'.bin2hex(random_bytes(16));
        $request = $this->requestFactory
            ->createRequest($method, $url)
            ->withHeader('Authorization', 'Bearer '.$this->accessTokenProvider->token())
            ->withHeader('User-Agent', $this->config->userAgent)
            ->withHeader('X-Request-Id', $requestId)->withHeader('X-Correlation-Id', $requestId)
            ->withHeader('API-Version', $this->config->apiVersion);

        foreach ($this->config->requestContext?->headers() ?? [] as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        foreach ($headers as $name => $value) {
            if ($value !== null && $value !== '') {
                $request = $request->withHeader((string) $name, (string) $value);
            }
        }

        if ($body !== null) {
            $json = json_encode($body, JSON_THROW_ON_ERROR);
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream($json));
        }

        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new WwsrapportException('The WWSrapport API request failed: '.$exception->getMessage(), 0, $exception);
        }
    }

    private function url(string $path, array $query): string
    {
        $path = '/'.ltrim($path, '/');
        $url = $this->config->normalizedBaseUrl().$path;

        if ($query !== []) {
            $url .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return $url;
    }

    /**
     * @throws ApiException
     */
    private function throwApiException(ResponseInterface $response, string $raw): never
    {
        $status = $response->getStatusCode();
        $problem = $this->decodeProblem($raw);
        $message = $this->errorMessage($status, $problem);
        $requestId = $this->requestId($response, $problem);

        $class = match (true) {
            $status === 400 && in_array((string) ($problem['code'] ?? ''), ['invalid_input', 'validation_error'], true) => ValidationException::class,
            $status === 401 => AuthenticationException::class,
            $status === 402 => PaymentRequiredException::class,
            $status === 403 => AuthorizationException::class,
            $status === 404 => NotFoundException::class,
            $status === 409 => ConflictException::class,
            $status === 422 => ValidationException::class,
            $status === 429 => RateLimitException::class,
            default => ApiException::class,
        };

        throw new $class($message, $status, $problem, $raw, $requestId);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeProblem(string $raw): ?array
    {
        if (trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>|null  $problem
     */
    private function errorMessage(int $status, ?array $problem): string
    {
        foreach (['detail', 'message', 'title', 'code'] as $field) {
            if (isset($problem[$field]) && is_scalar($problem[$field]) && (string) $problem[$field] !== '') {
                return (string) $problem[$field];
            }
        }

        return 'The WWSrapport API returned HTTP '.$status.'.';
    }

    /**
     * @param  array<string, mixed>|null  $problem
     */
    private function requestId(ResponseInterface $response, ?array $problem = null): ?string
    {
        if ($response->hasHeader('X-WWS-Request-Id')) {
            return $response->getHeaderLine('X-WWS-Request-Id');
        }
        if ($response->hasHeader('X-Request-Id')) {
            return $response->getHeaderLine('X-Request-Id');
        }

        if (isset($problem['meta']['request_id']) && is_scalar($problem['meta']['request_id'])) {
            return (string) $problem['meta']['request_id'];
        }

        if (isset($problem['request_id']) && is_scalar($problem['request_id'])) {
            return (string) $problem['request_id'];
        }

        return null;
    }
}
