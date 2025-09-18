<?php

namespace Sendy\Api\Http\Transport;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class TransportFactory
{
    public static function create(): TransportInterface
    {
        if (class_exists(\Symfony\Component\HttpClient\Psr18Client::class)) {
            try {
                return self::createSymfonyTransport();
            } catch (\LogicException $exception) {
                // Fall back to other transports if the Symfony HTTP Client cannot be used
            }
        }

        if (
            class_exists(\GuzzleHttp\Client::class)
            && is_subclass_of(\GuzzleHttp\Client::class, ClientInterface::class)
        ) {
            return self::createGuzzleTransport();
        }

        if (extension_loaded('curl')) {
            return self::createCurlTransport();
        }

        throw new \LogicException(
            'No suitable HTTP client found.',
        );
    }

    public static function createGuzzleTransport(): Psr18Transport
    {
        assert(class_exists(\GuzzleHttp\Psr7\HttpFactory::class));
        assert(class_exists(\GuzzleHttp\Client::class));
        assert(class_exists(\GuzzleHttp\Utils::class));

        $httpFactory = new \GuzzleHttp\Psr7\HttpFactory();
        $client = new \GuzzleHttp\Client();

        assert($client instanceof ClientInterface);
        assert($httpFactory instanceof RequestFactoryInterface);
        assert($httpFactory instanceof StreamFactoryInterface);
        assert($httpFactory instanceof UriFactoryInterface);

        return new Psr18Transport(
            $client,
            $httpFactory,
            $httpFactory,
            $httpFactory,
            \GuzzleHttp\Utils::defaultUserAgent(),
        );
    }

    public static function createSymfonyTransport(): Psr18Transport
    {
        assert(class_exists(\Symfony\Component\HttpClient\Psr18Client::class));

        $client = new \Symfony\Component\HttpClient\Psr18Client();

        assert($client instanceof ClientInterface);
        assert($client instanceof RequestFactoryInterface);
        assert($client instanceof StreamFactoryInterface);
        assert($client instanceof UriFactoryInterface);

        $userAgent = 'SymfonyHttpClient';

        if (class_exists(\Symfony\Component\HttpKernel\Kernel::class)) {
            $userAgent .= '/' . \Symfony\Component\HttpKernel\Kernel::VERSION;
        }

        return new Psr18Transport(
            $client,
            $client,
            $client,
            $client,
            $userAgent,
        );
    }

    public static function createCurlTransport(): CurlTransport
    {
        return new CurlTransport();
    }
}
