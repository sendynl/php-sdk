<?php

namespace Sendy\Api\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Resources\Parcelshop;
use Sendy\Api\Tests\TestsEndpoints;

class ParcelshopTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Parcelshop($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->list(['DHL'], 52.040588, 5.564890, 'NL', '3905KW'));

        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
        $this->assertEquals(
            'https://app.sendy.nl/api/parcel_shops' .
            '?carriers%5B0%5D=DHL&latitude=52.040588&longitude=5.56489&country=NL&postal_code=3905KW',
            $transport->getLastRequest()->getUrl()
        );
    }
}
