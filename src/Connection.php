<?php

namespace Sendy\Api;

use Psr\Http\Message\UriInterface;
use Sendy\Api\Exceptions\SendyException;
use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\TransportFactory;
use Sendy\Api\Http\Transport\TransportInterface;
use Sendy\Api\Resources\Resource;

/**
 * @property-read \Sendy\Api\Resources\Carrier $carrier
 * @property-read \Sendy\Api\Resources\Label $label
 * @property-read \Sendy\Api\Resources\Me $me
 * @property-read \Sendy\Api\Resources\Parcelshop $parcelshop
 * @property-read \Sendy\Api\Resources\Service $service
 * @property-read \Sendy\Api\Resources\Shipment $shipment
 * @property-read \Sendy\Api\Resources\ShippingPreference $shippingPreference
 * @property-read \Sendy\Api\Resources\Shop $shop
 * @property-read \Sendy\Api\Resources\Webhook $webhook
 */
class Connection
{
    public const VERSION = '3.0.0';

    public const BASE_URL = 'https://app.sendy.nl';

    private const API_URL = '/api';

    private const AUTH_URL = '/oauth/authorize';

    private const TOKEN_URL = '/oauth/token';

    private ?TransportInterface $transport = null;

    /** @var string The Client ID as UUID */
    private string $clientId;

    /** @var string The Client Secret */
    private string $clientSecret;

    /** @var string The authorization code which is returned after the OAuth flow */
    private string $authorizationCode;

    /** @var string Either the bearer token or the personal access token */
    private string $accessToken;

    /** @var int|null The UNIX-timestamp when the access token expires */
    private ?int $tokenExpires = null;

    /** @var string The token needed to fetch a new access token */
    private string $refreshToken;

    /** @var string The URL as configured with the OAuth client */
    private string $redirectUrl;

    /** @var string The appendix for the user agent */
    private string $userAgentAppendix = '';

    /** @var mixed */
    private $state = null;

    /** @var callable($this) */
    private $tokenUpdateCallback;

    private bool $oauthClient = false;

    public ?Meta $meta;

    public ?RateLimits $rateLimits;

    /**
     * @var array<string, list<string>>
     */
    public array $sendyHeaders = [];

    public function getTransport(): TransportInterface
    {
        if ($this->transport instanceof TransportInterface) {
            return $this->transport;
        }

        return $this->transport = TransportFactory::create();
    }

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function setUserAgentAppendix(string $userAgentAppendix): Connection
    {
        $this->userAgentAppendix = $userAgentAppendix;

        return $this;
    }

    public function setClientId(string $clientId): Connection
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function setClientSecret(string $clientSecret): Connection
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function setAuthorizationCode(string $authorizationCode): Connection
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): Connection
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getTokenExpires(): int
    {
        return $this->tokenExpires;
    }

    public function setTokenExpires(int $tokenExpires): Connection
    {
        $this->tokenExpires = $tokenExpires;

        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): Connection
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function setRedirectUrl(string $redirectUrl): Connection
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    /**
     * @param mixed|null $state
     * @return Connection
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function setTokenUpdateCallback(callable $tokenUpdateCallback): Connection
    {
        $this->tokenUpdateCallback = $tokenUpdateCallback;

        return $this;
    }

    /**
     * Build the URL to authorize the application
     */
    public function getAuthorizationUrl(): string
    {
        return self::BASE_URL . self::AUTH_URL . '?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code',
            'state' => $this->state,
        ]);
    }

    public function isOauthClient(): bool
    {
        return $this->oauthClient;
    }

    public function setOauthClient(bool $oauthClient): Connection
    {
        $this->oauthClient = $oauthClient;

        return $this;
    }

    /**
     * @throws SendyException
     */
    public function checkOrAcquireAccessToken(): void
    {
        if (empty($this->accessToken) || ($this->tokenHasExpired() && $this->isOauthClient())) {
            $this->acquireAccessToken();
        }
    }

    public function tokenHasExpired(): bool
    {
        // Starting July 1st, 2025 only short-lived access tokens will be issued from the API. This will invalidate all
        // tokens issued before July 1st with a lifespan longer than 10 minutes.
        if (time() > 1751320800 && $this->tokenExpires > time() + 600) {
            return true;
        }

        return $this->tokenExpires - 10 < time();
    }

    /**
     * @throws SendyException
     */
    private function acquireAccessToken(): void
    {
        if (empty($this->refreshToken)) {
            $parameters = [
                'redirect_uri' => $this->redirectUrl,
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $this->authorizationCode,
            ];
        } else {
            $parameters = [
                'refresh_token' => $this->refreshToken,
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ];
        }

        $body = $this->performRequest(
            $this->createRequest('POST', self::BASE_URL . self::TOKEN_URL, json_encode($parameters)),
            false,
        );

        $this->accessToken = $body['access_token'];
        $this->refreshToken = $body['refresh_token'];
        $this->tokenExpires = time() + $body['expires_in'];

        if (is_callable($this->tokenUpdateCallback)) {
            call_user_func($this->tokenUpdateCallback, $this);
        }
    }

    /**
     * @param array<string, string|string[]> $params
     * @param array<string, string> $headers
     */
    public function createRequest(
        string $method,
        string $endpoint,
        ?string $body = null,
        array $params = [],
        array $headers = []
    ): Request {
        $userAgent = sprintf("SendySDK/%s PHP/%s", self::VERSION, phpversion());

        if ($this->isOauthClient()) {
            $userAgent .= ' OAuth/2.0';
        }

        $userAgent .= " {$this->getTransport()->getUserAgent()} {$this->userAgentAppendix}";

        $headers = array_merge($headers, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => trim($userAgent),
        ]);

        if (! empty($params)) {
            $endpoint .= strpos($endpoint, '?') === false ? '?' : '&';
            $endpoint .= http_build_query($params);
        }

        return new Request($method, $endpoint, $headers, $body);
    }

    /**
     * @param UriInterface|string $url
     * @param array<string, mixed> $params
     * @param array<string, mixed> $headers
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function get($url, array $params = [], array $headers = []): array
    {
        $url = self::API_URL . $url;

        $request = $this->createRequest('GET', $url, null, $params, $headers);

        return $this->performRequest($request);
    }

    /**
     * @param UriInterface|string $url
     * @param array<string, mixed|mixed[]> $body
     * @param array<string, mixed|mixed[]> $params
     * @param array<string, mixed|mixed[]> $headers
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function post($url, ?array $body = null, array $params = [], array $headers = []): array
    {
        $url = self::API_URL . $url;

        if (! is_null($body)) {
            $body = json_encode($body);
        }

        $request = $this->createRequest('POST', $url, $body, $params, $headers);

        return $this->performRequest($request);
    }

    /**
     * @param UriInterface|string $url
     * @param array<string, mixed|array<string, mixed>> $body
     * @param array<string, mixed|array<string, mixed>> $params
     * @param array<string, mixed|array<string, mixed>> $headers
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function put($url, array $body = [], array $params = [], array $headers = []): array
    {
        $url = self::API_URL . $url;
        $body = json_encode($body);

        $request = $this->createRequest('PUT', $url, $body, $params, $headers);

        return $this->performRequest($request);
    }

    /**
     * @param UriInterface|string $url
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function delete($url): array
    {
        $url = self::API_URL . $url;

        $request = $this->createRequest('DELETE', $url, null, [], []);

        return $this->performRequest($request);
    }

    /**
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    private function performRequest(Request $request, bool $checkAccessToken = true): array
    {
        if ($checkAccessToken) {
            $this->checkOrAcquireAccessToken();

            $request->setHeader('Authorization', "Bearer {$this->accessToken}");
        }

        $response = $this->getTransport()->send($request);

        return $this->parseResponse($response, $request);
    }

    /**
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function parseResponse(Response $response, Request $request): array
    {
        $this->extractRateLimits($response);
        $this->extractSendyHeaders($response);

        if ($exception = $response->toException($request)) {
            throw $exception;
        }

        if ($response->getStatusCode() === 204) {
            return [];
        }

        $json = $response->getDecodedBody();

        if (array_key_exists('data', $json)) {
            if (array_key_exists('meta', $json)) {
                $this->meta = Meta::buildFromResponse($json['meta']);
            } else {
                $this->meta = null;
            }

            return $json['data'];
        }

        return $json;
    }

    private function extractRateLimits(Response $response): void
    {
        $this->rateLimits = RateLimits::buildFromResponse($response);
    }

    /**
     * Extract the x-sendy-* headers from the response.
     */
    private function extractSendyHeaders(Response $response): void
    {
        $this->sendyHeaders = array_filter(
            $response->getHeaders(),
            fn(string $key) => substr($key, 0, 8) === 'x-sendy-',
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Magic method to fetch the resource object
     */
    public function __get(string $resource): Resource
    {
        $className = "Sendy\\Api\\Resources\\" . ucfirst($resource);

        if (! class_exists($className)) {
            throw new \InvalidArgumentException("Resource '" . ucfirst($resource) . "' does not exist");
        }

        return new $className($this);
    }
}
