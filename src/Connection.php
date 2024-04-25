<?php

namespace Sendy\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
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
 */
class Connection
{
    private const BASE_URL = 'https://app.sendy.nl';

    private const API_URL = '/api';

    private const AUTH_URL = '/oauth/authorize';

    private const TOKEN_URL = '/oauth/token';

    private const VERSION = '1.0.1';

    /** @var Client|null */
    private ?Client $client = null;

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

    /** @var callable(Client) */
    private $tokenUpdateCallback;

    /** @var bool */
    private bool $oauthClient = false;

    public ?Meta $meta;

    public ?RateLimits $rateLimits;

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        if ($this->client instanceof Client) {
            return $this->client;
        }

        $userAgent = sprintf("Sendy/%s PHP/%s", self::VERSION, phpversion());

        if ($this->isOauthClient()) {
            $userAgent .= ' OAuth/2.0';
        }

        $userAgent .= " {$this->userAgentAppendix}";

        $this->client = new Client([
            'http_errors' => true,
            'expect' => false,
            'base_uri' => self::BASE_URL,
            'headers' => [
                'User-Agent' => trim($userAgent),
            ]
        ]);

        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $userAgentAppendix
     * @return Connection
     */
    public function setUserAgentAppendix(string $userAgentAppendix): Connection
    {
        $this->userAgentAppendix = $userAgentAppendix;

        return $this;
    }

    /**
     * @param string $clientId
     * @return Connection
     */
    public function setClientId(string $clientId): Connection
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @param string $clientSecret
     * @return Connection
     */
    public function setClientSecret(string $clientSecret): Connection
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * @param string $authorizationCode
     * @return Connection
     */
    public function setAuthorizationCode(string $authorizationCode): Connection
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return Connection
     */
    public function setAccessToken(string $accessToken): Connection
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return int
     */
    public function getTokenExpires(): int
    {
        return $this->tokenExpires;
    }

    /**
     * @param int $tokenExpires
     * @return Connection
     */
    public function setTokenExpires(int $tokenExpires): Connection
    {
        $this->tokenExpires = $tokenExpires;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return Connection
     */
    public function setRefreshToken(string $refreshToken): Connection
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @param string $redirectUrl
     * @return Connection
     */
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

    /**
     * @param callable $tokenUpdateCallback
     * @return Connection
     */
    public function setTokenUpdateCallback(callable $tokenUpdateCallback): Connection
    {
        $this->tokenUpdateCallback = $tokenUpdateCallback;

        return $this;
    }

    /**
     * Build the URL to authorize the application
     *
     * @return string
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

    /**
     * @return bool
     */
    public function isOauthClient(): bool
    {
        return $this->oauthClient;
    }

    /**
     * @param bool $oauthClient
     * @return Connection
     */
    public function setOauthClient(bool $oauthClient): Connection
    {
        $this->oauthClient = $oauthClient;

        return $this;
    }

    public function checkOrAcquireAccessToken(): void
    {
        if (empty($this->accessToken) || ($this->tokenHasExpired() && $this->isOauthClient())) {
            $this->acquireAccessToken();
        }
    }

    public function tokenHasExpired(): bool
    {
        return $this->tokenExpires - 10 < time();
    }

    private function acquireAccessToken(): void
    {
        try {
            if (empty($this->refreshToken)) {
                $parameters = [
                    'redirect_uri'  => $this->redirectUrl,
                    'grant_type'    => 'authorization_code',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code'          => $this->authorizationCode,
                ];
            } else {
                $parameters = [
                    'refresh_token' => $this->refreshToken,
                    'grant_type'    => 'refresh_token',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ];
            }

            $response = $this->getClient()->post(self::BASE_URL . self::TOKEN_URL, ['form_params' => $parameters]);

            Message::rewindBody($response);

            $responseBody = $response->getBody()->getContents();

            $body = json_decode($responseBody, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->accessToken = $body['access_token'];
                $this->refreshToken = $body['refresh_token'];
                $this->tokenExpires = time() + $body['expires_in'];

                if (is_callable($this->tokenUpdateCallback)) {
                    call_user_func($this->tokenUpdateCallback, $this);
                }
            } else {
                throw new ApiException('Could not acquire tokens, json decode failed. Got response: ' . $responseBody);
            }
        } catch (BadResponseException $e) {
            throw new ApiException('Something went wrong. Got: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param null|StreamInterface|resource|string $body
     * @param array<string, string|string[]>|null $params
     * @param array<string, string|string[]>|null $headers
     * @return Request
     */
    private function createRequest(
        string $method,
        string $endpoint,
        $body = null,
        array $params = null,
        array $headers = null
    ): Request {
        $headers = array_merge($headers, [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $this->checkOrAcquireAccessToken();

        $headers['Authorization'] = "Bearer {$this->accessToken}";

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
     * @throws ApiException
     * @throws GuzzleException
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
     * @throws ApiException
     * @throws GuzzleException
     */
    public function post($url, array $body = null, array $params = [], array $headers = []): array
    {
        $url = self::API_URL . $url;

        if (!is_null($body)) {
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
     * @throws ApiException
     * @throws GuzzleException
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
     * @throws ApiException|\GuzzleHttp\Exception\GuzzleException
     */
    public function delete($url): array
    {
        $url = self::API_URL . $url;

        $request = $this->createRequest('DELETE', $url, null, [], []);

        return $this->performRequest($request);
    }

    /**
     * @param Request $request
     * @return mixed[]|\mixed[][]|\string[][]
     * @throws ApiException
     * @throws GuzzleException
     */
    private function performRequest(Request $request): array
    {
        try {
            $response = $this->getClient()->send($request);

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            $this->parseException($e);
        }
    }

    /**
     * @param ResponseInterface $response
     * @return array<string, mixed|array<string|mixed>>
     * @throws ApiException
     */
    public function parseResponse(ResponseInterface $response): array
    {
        $this->extractRateLimits($response);

        if ($response->getStatusCode() === 204) {
            return [];
        }

        Message::rewindBody($response);

        $responseBody = $response->getBody()->getContents();

        $json = json_decode($responseBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Json decode failed. Got: " . $responseBody);
        }

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

    /**
     * @param \Exception $e
     * @return void
     * @throws ApiException
     */
    public function parseException(\Exception $e): void
    {
        if (! $e instanceof BadResponseException) {
            throw new ApiException($e->getMessage(), 0, $e);
        }

        $this->extractRateLimits($e->getResponse());

        if ($e instanceof ServerException) {
            throw new ApiException($e->getMessage(), $e->getResponse()->getStatusCode());
        }

        $response = $e->getResponse();

        Message::rewindBody($response);

        $responseBody = $response->getBody()->getContents();

        $json = json_decode($responseBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException("Json decode failed. Got: " . $responseBody);
        }

        if (array_key_exists('errors', $json)) {
            throw new ApiException($json['message'], 0, $e, $json['errors']);
        }

        throw new ApiException($json['message']);
    }

    private function extractRateLimits(ResponseInterface $response): void
    {
        $this->rateLimits = RateLimits::buildFromResponse($response);
    }

    /**
     * Magic method to fetch the resource object
     *
     * @param string $resource
     * @return Resource
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
