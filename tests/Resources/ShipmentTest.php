<?php

namespace Sendy\Api\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Resources\Shipment;
use Sendy\Api\Tests\TestsEndpoints;

class ShipmentTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('https://app.sendy.nl/api/shipments?page=1', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }

    public function testGet(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->get('1337'));

        $this->assertEquals('https://app.sendy.nl/api/shipments/1337', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }

    public function testUpdate(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->update('1337', ['foo' => 'bar']));

        $this->assertEquals('https://app.sendy.nl/api/shipments/1337', $transport->getLastRequest()->getUrl());
        $this->assertEquals('PUT', $transport->getLastRequest()->getMethod());
        $this->assertEquals('{"foo":"bar"}', $transport->getLastRequest()->getBody());
    }

    public function testDelete(): void
    {
        $transport = new MockTransport(
            new Response(204, [], ''),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->delete('1337'));

        $this->assertEquals('https://app.sendy.nl/api/shipments/1337', $transport->getLastRequest()->getUrl());
        $this->assertEquals('DELETE', $transport->getLastRequest()->getMethod());
    }

    public function testCreateFromPreference(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->createFromPreference(['foo' => 'bar'], false));

        $this->assertEquals(
            'https://app.sendy.nl/api/shipments/preference?generateDirectly=0',
            $transport->getLastRequest()->getUrl(),
        );
        $this->assertEquals('POST', $transport->getLastRequest()->getMethod());
        $this->assertEquals('{"foo":"bar"}', $transport->getLastRequest()->getBody());
    }

    public function testCreateAndGenerateFromPreference(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->createFromPreference(['foo' => 'bar']));
        $this->assertEquals(
            'https://app.sendy.nl/api/shipments/preference?generateDirectly=1',
            $transport->getLastRequest()->getUrl(),
        );
    }

    public function testCreateWithSmartRules(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode(['foo' => 'bar'])),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals(['foo' => 'bar'], $resource->createWithSmartRules(['foo' => 'bar']));

        $this->assertEquals('https://app.sendy.nl/api/shipments/smart-rule', $transport->getLastRequest()->getUrl());
        $this->assertEquals('POST', $transport->getLastRequest()->getMethod());
        $this->assertEquals('{"foo":"bar"}', $transport->getLastRequest()->getBody());
    }

    public function testGenerateAsynchronous(): void
    {
        $transport = new MockTransport(
            new Response(200, [], '{}'),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->generate('1337'));

        $this->assertEquals('https://app.sendy.nl/api/shipments/1337/generate', $transport->getLastRequest()->getUrl());
        $this->assertEquals('POST', $transport->getLastRequest()->getMethod());
        $this->assertEquals('{"asynchronous":true}', $transport->getLastRequest()->getBody());
    }

    public function testGenerateSynchronous(): void
    {
        $transport = new MockTransport(
            new Response(200, [], '{}'),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->generate('1337', false));

        $this->assertEquals('https://app.sendy.nl/api/shipments/1337/generate', $transport->getLastRequest()->getUrl());
        $this->assertEquals('POST', $transport->getLastRequest()->getMethod());
        $this->assertEquals('{"asynchronous":false}', $transport->getLastRequest()->getBody());
    }

    public function testLabels(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->labels('1337'));

        $this->assertEquals('https://app.sendy.nl/api/shipments/1337/labels', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }

    public function testDocuments(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Shipment($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->documents('1337'));

        $this->assertEquals(
            'https://app.sendy.nl/api/shipments/1337/documents',
            $transport->getLastRequest()->getUrl(),
        );
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }
}
