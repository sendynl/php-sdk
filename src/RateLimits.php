<?php

namespace Sendy\Api;

use Psr\Http\Message\ResponseInterface;

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

    public static function buildFromResponse(ResponseInterface $response): RateLimits
    {
        return new self(
            (int) implode("", $response->getHeader('Retry-After')),
            (int) implode("", $response->getHeader('X-RateLimit-Limit')),
            (int) implode("", $response->getHeader('X-RateLimit-Remaining')),
            (int) implode("", $response->getHeader('X-RateLimit-Reset'))
        );
    }
}
