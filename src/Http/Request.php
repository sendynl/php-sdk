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

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null
    ) {
        $this->method = strtoupper($method);
        $this->url = $url;
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->body = $body;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        if (substr($this->url, 0, 1) === '/') {
            return Connection::BASE_URL . $this->url;
        }

        return $this->url;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setHeader(string $name, string $value): void
    {
        $this->headers[strtolower($name)] = $value;
    }
}
