<?php

namespace Sendy\Api\Tests\Resources;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Resources\Webhook;
use PHPUnit\Framework\TestCase;
use Sendy\Api\Tests\TestsEndpoints;

class WebhookTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $handler = new MockHandler([
            new Response(200, [], json_encode([])),
        ]);

        $resource = new Webhook($this->buildConnectionWithMockHandler($handler));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('/api/webhooks', (string)$handler->getLastRequest()->getUri());
        $this->assertEquals('GET', $handler->getLastRequest()->getMethod());
    }

    public function testDelete(): void
    {
        $handler = new MockHandler([
            new Response(204),
        ]);

        $resource = new Webhook($this->buildConnectionWithMockHandler($handler));

        $resource->delete('webhook-id');

        $this->assertEquals('/api/webhooks/webhook-id', $handler->getLastRequest()->getUri());
        $this->assertEquals('DELETE', $handler->getLastRequest()->getMethod());
    }

    public function testCreate(): void
    {
        $handler = new MockHandler([
            new Response(201, [], json_encode([
                'data' => [
                    'id' => 'webhook-id',
                    'url' => 'https://example.com/webhook',
                    'events' => [
                        'shipment.generated',
                    ]
                ]
            ])),
        ]);

        $resource = new Webhook($this->buildConnectionWithMockHandler($handler));

        $resource->create([
            'url' => 'https://example.com/webhook',
            'events' => [
                'shipments.generated',
            ],
        ]);

        $this->assertEquals('/api/webhooks', (string)$handler->getLastRequest()->getUri());
        $this->assertEquals('POST', $handler->getLastRequest()->getMethod());
        $this->assertEquals(
            '{"url":"https:\/\/example.com\/webhook","events":["shipments.generated"]}',
            $handler->getLastRequest()->getBody()->getContents()
        );
    }

    public function testUpdate(): void
    {
        $handler = new MockHandler([
            new Response(201, [], json_encode([
                'data' => [
                    'id' => 'webhook-id',
                    'url' => 'https://example.com/updated-webhook',
                    'events' => [
                        'shipment.generated',
                    ]
                ]
            ])),
        ]);

        $resource = new Webhook($this->buildConnectionWithMockHandler($handler));

        $resource->update('webhook-id', [
            'url' => 'https://example.com/updated-webhook',
            'events' => [
                'shipment.generated',
            ],
        ]);

        $this->assertEquals('/api/webhooks/webhook-id', $handler->getLastRequest()->getUri());
        $this->assertEquals('PUT', $handler->getLastRequest()->getMethod());
        $this->assertEquals(
            '{"url":"https:\/\/example.com\/updated-webhook","events":["shipment.generated"]}',
            $handler->getLastRequest()->getBody()->getContents()
        );
    }
}
