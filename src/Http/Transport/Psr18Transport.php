<?php

namespace Sendy\Api\Http\Transport;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

class Psr18Transport implements TransportInterface
{
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private UriFactoryInterface $uriFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        UriFactoryInterface $uriFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->uriFactory = $uriFactory;
    }

    public function send(Request $request): Response
    {
        $psrRequest = $this->requestFactory->createRequest(
            $request->getMethod(),
            $this->uriFactory->createUri($request->getUrl())
        );

        foreach ($request->getHeaders() as $name => $value) {
            $psrRequest = $psrRequest->withHeader($name, $value);
        }

        if ($body = $request->getBody()) {
            $psrRequest = $psrRequest->withBody(
                $this->streamFactory->createStream($body)
            );
        }

        try {
            $psrResponse = $this->client->sendRequest($psrRequest);
        } catch (\Throwable $e) {
            throw new \Sendy\Api\Exceptions\TransportException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response(
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders(),
            (string) $psrResponse->getBody(),
        );
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    public function getUserAgent(): string
    {
        return 'PSR-18 (' . str_replace('\\', '_', get_class($this->client)) . ')';
    }
}
