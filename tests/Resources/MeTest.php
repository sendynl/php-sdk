<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Resources\Label;
use Sendy\Api\Resources\Me;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class MeTest extends TestCase
{
    use TestsEndpoints;

    public function testGet(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Me($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->get());

        $this->assertEquals('/api/me', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }
}
