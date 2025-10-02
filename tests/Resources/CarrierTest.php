<?php

namespace Sendy\Api\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Resources\Carrier;
use Sendy\Api\Tests\TestsEndpoints;

class CarrierTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Carrier($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('https://app.sendy.nl/api/carriers', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }

    public function testGet(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Carrier($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->get(1));

        $this->assertEquals('https://app.sendy.nl/api/carriers/1', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }
}
