<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Resources\Carrier;
use Sendy\Api\Resources\Label;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class LabelTest extends TestCase
{
    use TestsEndpoints;

    public function testGet(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Label($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->get(['123456']));

        $this->assertEquals(
            '/api/labels?ids%5B0%5D=123456',
            (string) $handler->getLastRequest()->getUri()
        );

        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }

    public function testParametersAreSetInURL(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Label($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->get(['123456', 'A4', 'top-left']));

        $this->assertEquals(
            '/api/labels?ids%5B0%5D=123456&ids%5B1%5D=A4&ids%5B2%5D=top-left',
            (string) $handler->getLastRequest()->getUri()
        );

        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }
}
