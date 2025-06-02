<?php

namespace Sendy\Api\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Resources\ShippingPreference;
use Sendy\Api\Tests\TestsEndpoints;

class ShippingPreferenceTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new ShippingPreference($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('https://app.sendy.nl/api/shipping_preferences', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }
}
