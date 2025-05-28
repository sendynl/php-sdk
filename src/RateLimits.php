<?php

namespace Sendy\Api;

use Psr\Http\Message\ResponseInterface;
use Sendy\Api\Http\Response;

final class RateLimits
{
    public int $retryAfter;

    public int $limit;

    public int $remaining;

    public int $reset;

    /**
     * @param int $retryAfter
     * @param int $limit
     * @param int $remaining
     * @param int $reset
     */
    public function __construct(int $retryAfter, int $limit, int $remaining, int $reset)
    {
        $this->retryAfter = $retryAfter;
        $this->limit = $limit;
        $this->remaining = $remaining;
        $this->reset = $reset;
    }

    public static function buildFromResponse(Response $response): RateLimits
    {
        $headers = $response->getHeaders();

        return new self(
            (int) ($headers['retry-after'][0] ?? 0),
            (int) ($headers['x-ratelimit-limit'][0] ?? 0),
            (int) ($headers['x-ratelimit-remaining'][0] ?? 0),
            (int) ($headers['x-ratelimit-reset'][0] ?? 0)
        );
    }
}
