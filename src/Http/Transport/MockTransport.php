<?php

namespace Sendy\Api\Http\Transport;

use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

class MockTransport implements TransportInterface
{
    private ?Response $response;

    /**
     * @var Request[]
     */
    private $requests = [];

    public function __construct(?Response $response = null)
    {
        $this->response = $response ?? new Response(200, [], json_encode(['success' => true]));
    }

    public function send(Request $request): Response
    {
        $this->requests[] = $request;

        return $this->response;
    }

    public function getUserAgent(): string
    {
        return 'MockTransport/1.0';
    }

    public function getLastRequest(): ?Request
    {
        return end($this->requests) ?: null;
    }
}
