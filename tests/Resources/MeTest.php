<?php

namespace Sendy\Api\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Resources\Me;
use Sendy\Api\Tests\TestsEndpoints;

class MeTest extends TestCase
{
    use TestsEndpoints;

    public function testGet(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Me($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->get());

        $this->assertEquals('https://app.sendy.nl/api/me', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }
}
