<?php

namespace Sendy\Api\Tests;

use Sendy\Api\Http\Response;
use Sendy\Api\RateLimits;
use PHPUnit\Framework\TestCase;

class RateLimitsTest extends TestCase
{
    public function testBuildFromResponseBuildsRateLimitsObject(): void
    {
        $response = new Response(
            200,
            [
                'Retry-After' => '59',
                'X-RateLimit-Limit' => '180',
                'X-RateLimit-Remaining' => '179',
                'X-RateLimit-Reset' => '1681381136',
            ],
            ''
        );

        $this->assertInstanceOf(RateLimits::class, RateLimits::buildFromResponse($response));
        $this->assertEquals(59, RateLimits::buildFromResponse($response)->retryAfter);
        $this->assertEquals(180, RateLimits::buildFromResponse($response)->limit);
        $this->assertEquals(179, RateLimits::buildFromResponse($response)->remaining);
        $this->assertEquals(1681381136, RateLimits::buildFromResponse($response)->reset);
    }
}
