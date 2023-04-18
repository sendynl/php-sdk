<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Resources\Shipment;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class ShipmentTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Shipment($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('/api/shipments?page=1', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }

    public function testGet(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Shipment($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->get('1337'));

        $this->assertEquals('/api/shipments/1337', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }

    public function testUpdate(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Shipment($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->update('1337', ['foo' => 'bar']));

        $this->assertEquals('/api/shipments/1337', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('PUT', $handler->getLastRequest()->getMethod());
        $this->assertEquals('{"foo":"bar"}', $handler->getLastRequest()->getBody()->getContents());
    }

    public function testDelete(): void
    {
        $handler = new MockHandler([
            new Response(204),
        ]);

        $resource = new Shipment($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->delete('1337'));

        $this->assertEquals('/api/shipments/1337', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('DELETE', $handler->getLastRequest()->getMethod());
    }

    public function testCreateFromPreference(): void
    {
        $handler = new MockHandler([
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);

        $resource = new Shipment($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->createFromPreference(['foo' => 'bar'], false));

        $this->assertEquals(
            '/api/shipments/preference?generateDirectly=0',
            (string) $handler->getLastRequest()->getUri()
        );
        $this->assertEquals('POST', $handler->getLastRequest()->getMethod());
        $this->assertEquals('{"foo":"bar"}', $handler->getLastRequest()->getBody()->getContents());

        $this->assertEquals([], $resource->createFromPreference(['foo' => 'bar']));
        $this->assertEquals(
            '/api/shipments/preference?generateDirectly=1',
            (string) $handler->getLastRequest()->getUri()
        );
    }

    public function testGenerate(): void
    {
        $handler = new MockHandler([
            new Response(200, [], '{}'),
            new Response(200, [], '{}'),
        ]);

        $resource = new Shipment($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->generate('1337'));

        $this->assertEquals('/api/shipments/1337/generate', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('POST', $handler->getLastRequest()->getMethod());
        $this->assertEquals('{"asynchronous":true}', $handler->getLastRequest()->getBody()->getContents());

        $this->assertEquals([], $resource->generate('1337', false));

        $this->assertEquals('/api/shipments/1337/generate', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('{"asynchronous":false}', $handler->getLastRequest()->getBody()->getContents());
    }

    public function testLabels(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Shipment($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->labels('1337'));

        $this->assertEquals('/api/shipments/1337/labels', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }

    public function testDocuments(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Shipment($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->documents('1337'));

        $this->assertEquals('/api/shipments/1337/documents', (string) $handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }
}
