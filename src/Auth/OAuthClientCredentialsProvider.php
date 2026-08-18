<?php

declare(strict_types=1);

namespace Wwsrapport\Client\Auth;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Wwsrapport\Client\Exception\AuthenticationException;

final class OAuthClientCredentialsProvider implements AccessTokenProvider
{
    private ?string $accessToken = null;
    private int $expiresAt = 0;

    /** @param list<string> $scopes */
    public function __construct(
        private readonly string $clientId, private readonly string $clientSecret, private readonly string $tokenUrl,
        private readonly ClientInterface $http, private readonly RequestFactoryInterface $requests,
        private readonly StreamFactoryInterface $streams, private readonly array $scopes = [],
    ) {}

    public function token(): string
    {
        if ($this->accessToken !== null && time() < $this->expiresAt - 30) {
            return $this->accessToken;
        }
        $form = ['grant_type' => 'client_credentials'];
        if ($this->scopes !== []) $form['scope'] = implode(' ', $this->scopes);
        $request = $this->requests->createRequest('POST', $this->tokenUrl)
            ->withHeader('Authorization', 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret))
            ->withHeader('Accept', 'application/json')->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streams->createStream(http_build_query($form, '', '&', PHP_QUERY_RFC3986)));
        $response = $this->http->sendRequest($request);
        $raw = (string) $response->getBody();
        $payload = json_decode($raw, true);
        if ($response->getStatusCode() !== 200 || ! is_array($payload) || ! is_string($payload['access_token'] ?? null)) {
            throw new AuthenticationException('The WWSrapport OAuth server did not issue an access token.', $response->getStatusCode(), is_array($payload) ? $payload : null, $raw);
        }
        $this->accessToken = $payload['access_token'];
        $this->expiresAt = time() + max(60, (int) ($payload['expires_in'] ?? 300));

        return $this->accessToken;
    }
}
