<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Connection;
use Sendy\Api\Resources\Carrier;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class CarrierTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Carrier($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('/api/carriers', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }

    public function testGet(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Carrier($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->get(1));

        $this->assertEquals('/api/carriers/1', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }
}
