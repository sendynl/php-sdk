<?php

namespace Sendy\Api\Http;

use Sendy\Api\Connection;

final class Request
{
    /**
     * @var 'GET'|'POST'|'PUT'|'DELETE'|'PATCH'|'HEAD'|'OPTIONS'
     */
    private string $method;

    private string $url;

    /**
     * @var array<string, string>
     */
    private array $headers;

    private ?string $body;

    public function __construct(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null
    ) {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        if (str_starts_with($this->url, '/')) {
            return Connection::BASE_URL . $this->url;
        }

        return $this->url;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }
}
