<?php

namespace Sendy\Api\Tests\Resources;

use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Resources\Service;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class ServiceTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Service($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->list(1337));

        $this->assertEquals('https://app.sendy.nl/api/carriers/1337/services', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }
}
