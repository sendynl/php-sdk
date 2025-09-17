<?php

namespace Sendy\Api\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Resources\Label;
use Sendy\Api\Tests\TestsEndpoints;

class LabelTest extends TestCase
{
    use TestsEndpoints;

    public function testGet(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Label($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->get(['123456']));

        $this->assertEquals(
            'https://app.sendy.nl/api/labels?ids%5B0%5D=123456',
            $transport->getLastRequest()->getUrl(),
        );

        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }

    public function testParametersAreSetInURL(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Label($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->get(['123456', 'A4', 'top-left']));

        $this->assertEquals(
            'https://app.sendy.nl/api/labels?ids%5B0%5D=123456&ids%5B1%5D=A4&ids%5B2%5D=top-left',
            $transport->getLastRequest()->getUrl(),
        );

        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }
}
