<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Resources\Service;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class ServiceTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Service($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->list(1337));

        $this->assertEquals('/api/carriers/1337/services', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }
}
