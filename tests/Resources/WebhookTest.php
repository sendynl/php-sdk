<?php

namespace Sendy\Api\Tests\Resources;

use PHPUnit\Framework\TestCase;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Resources\Webhook;
use Sendy\Api\Tests\TestsEndpoints;

class WebhookTest extends TestCase
{
    use TestsEndpoints;

    public function testList(): void
    {
        $transport = new MockTransport(
            new Response(200, [], json_encode([])),
        );

        $resource = new Webhook($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->list());

        $this->assertEquals('https://app.sendy.nl/api/webhooks', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }

    public function testDelete(): void
    {
        $transport = new MockTransport(
            new Response(204, [], ''),
        );

        $resource = new Webhook($this->buildConnectionWithMockTransport($transport));

        $this->assertEquals([], $resource->delete('webhook-id'));

        $this->assertEquals('https://app.sendy.nl/api/webhooks/webhook-id', $transport->getLastRequest()->getUrl());
        $this->assertEquals('DELETE', $transport->getLastRequest()->getMethod());
    }

    public function testCreate(): void
    {
        $transport = new MockTransport(
            new Response(201, [], json_encode([
                'data' => [
                    'id' => 'webhook-id',
                    'url' => 'https://example.com/webhook',
                    'events' => [
                        'shipment.generated',
                    ]
                ]
            ])),
        );

        $resource = new Webhook($this->buildConnectionWithMockTransport($transport));

        $resource->create([
            'url' => 'https://example.com/webhook',
            'events' => [
                'shipments.generated',
            ],
        ]);

        $this->assertEquals('https://app.sendy.nl/api/webhooks', $transport->getLastRequest()->getUrl());
        $this->assertEquals('POST', $transport->getLastRequest()->getMethod());
        $this->assertEquals(
            '{"url":"https:\/\/example.com\/webhook","events":["shipments.generated"]}',
            $transport->getLastRequest()->getBody()
        );
    }

    public function testUpdate(): void
    {
        $transport = new MockTransport(
            new Response(201, [], json_encode([
                'data' => [
                    'id' => 'webhook-id',
                    'url' => 'https://example.com/updated-webhook',
                    'events' => [
                        'shipment.generated',
                    ]
                ]
            ])),
        );

        $resource = new Webhook($this->buildConnectionWithMockTransport($transport));

        $resource->update('webhook-id', [
            'url' => 'https://example.com/updated-webhook',
            'events' => [
                'shipment.generated',
            ],
        ]);

        $this->assertEquals('https://app.sendy.nl/api/webhooks/webhook-id', $transport->getLastRequest()->getUrl());
        $this->assertEquals('PUT', $transport->getLastRequest()->getMethod());
        $this->assertEquals(
            '{"url":"https:\/\/example.com\/updated-webhook","events":["shipment.generated"]}',
            $transport->getLastRequest()->getBody()
        );
    }
}
