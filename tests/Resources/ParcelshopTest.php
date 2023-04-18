<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Resources\Parcelshop;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class ParcelshopTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Parcelshop($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->list(['DHL'], 52.040588, 5.564890, 'NL', '3905KW'));

        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
        $this->assertEquals(
            '/api/parcel_shops?carriers%5B0%5D=DHL&latitude=52.040588&longitude=5.56489&country=NL&postal_code=3905KW',
            (string) $handler->getLastRequest()->getUri()
        );
    }
}
