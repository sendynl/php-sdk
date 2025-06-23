<?php

namespace Sendy\Api\Http\Transport;

use Psr\Http\Client\ClientInterface;

final class TransportFactory
{
    public static function create(): TransportInterface
    {
        if (class_exists(\Symfony\Component\HttpClient\HttpClient::class)) {
            try {
                return self::createSymfonyTransport();
            } catch (\LogicException $exception) {
                // Fall back to other transports if the Symfony HTTP Client cannot be used
            }
        }

        if (
            class_exists(\GuzzleHttp\Client::class) &&
            is_subclass_of(\GuzzleHttp\Client::class, ClientInterface::class)
        ) {
            return self::createGuzzleTransport();
        }

        if (extension_loaded('curl')) {
            return self::createCurlTransport();
        }

        throw new \LogicException(
            'No suitable HTTP client found.'
        );
    }

    public static function createGuzzleTransport(): Psr18Transport
    {
        $httpFactory = new \GuzzleHttp\Psr7\HttpFactory();

        return new Psr18Transport(
            new \GuzzleHttp\Client(),
            $httpFactory,
            $httpFactory,
            $httpFactory,
            \GuzzleHttp\Utils::defaultUserAgent()
        );
    }

    public static function createSymfonyTransport(): Psr18Transport
    {
        $client = new \Symfony\Component\HttpClient\Psr18Client();

        $userAgent = 'SymfonyHttpClient';

        if (class_exists(\Symfony\Component\HttpKernel\Kernel::class)) {
            $userAgent .= '/' . \Symfony\Component\HttpKernel\Kernel::VERSION;
        }

        return new Psr18Transport(
            $client,
            $client,
            $client,
            $client,
            $userAgent
        );
    }

    public static function createCurlTransport(): CurlTransport
    {
        return new CurlTransport();
    }
}
