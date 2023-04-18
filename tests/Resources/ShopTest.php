<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Resources\Shop;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class ShopTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Shop($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('/api/shops', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }

    public function testGet(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Shop($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->get('1337'));

        $this->assertEquals('/api/shops/1337', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }
}
