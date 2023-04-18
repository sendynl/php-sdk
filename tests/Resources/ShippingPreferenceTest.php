<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Resources\ShippingPreference;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Resources\Shop;
use Sendy\Api\Tests\TestsEndpoints;

class ShippingPreferenceTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new ShippingPreference($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('/api/shipping_preferences', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }
}
