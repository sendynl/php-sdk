<?php

namespace Sendy\Api\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sendy\Api\ApiException;
use Sendy\Api\Connection;
use Sendy\Api\Meta;
use Sendy\Api\Resources\Me;

class ConnectionTest extends TestCase
{
    public function testUserAgentIsSet(): void
    {
        $connection = new Connection();

        $this->assertEquals(
            sprintf('Sendy/1.0.2 PHP/%s', phpversion()),
            $connection->getTransport()->getConfig('headers')['User-Agent']
        );

        $connection = new Connection();
        $connection->setUserAgentAppendix('WooCommerce/6.2');

        $this->assertEquals(
            sprintf('Sendy/1.0.2 PHP/%s WooCommerce/6.2', phpversion()),
            $connection->getTransport()->getConfig('headers')['User-Agent']
        );

        $connection = new Connection();
        $connection->setOauthClient(true);

        $this->assertEquals(
            sprintf('Sendy/1.0.2 PHP/%s OAuth/2.0', phpversion()),
            $connection->getTransport()->getConfig('headers')['User-Agent']
        );
    }

    public function testTokenExpires(): void
    {
        $connection = new Connection();

        $connection->setTokenExpires(time() - 3600);

        $this->assertTrue($connection->tokenHasExpired());

        $connection->setTokenExpires(time() + 3600);

        $this->assertFalse($connection->tokenHasExpired());
    }

    public function testMagicGetterThrowsExceptionForInvalidResource(): void
    {
        $connection = new Connection();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Resource 'InvalidResource' does not exist");

        // @phpstan-ignore-next-line
        $connection->invalidResource;
    }

    public function testMagicGetterReturnsResource(): void
    {
        $connection = new Connection();

        $this->assertInstanceOf(Me::class, $connection->me);
    }

    public function testAuthorizationUrlIsBuilt(): void
    {
        $connection = new Connection();

        $connection->setClientId('client-id');
        $connection->setRedirectUrl('https://example.com');
        $connection->setState('state');

        // phpcs:disable
        $this->assertEquals(
            'https://app.sendy.nl/oauth/authorize?client_id=client-id&redirect_uri=https%3A%2F%2Fexample.com&response_type=code&state=state',
            $connection->getAuthorizationUrl()
        );
        // phpcs:enable
    }

    public function testParseResponseReturnsEmptyArrayWhenResponseHasNoContent(): void
    {
        $connection = new Connection();

        $response = new Response(204);

        $this->assertEquals([], $connection->parseResponse($response));
    }

    public function testParseResponseThrowsApiExceptionWithInvalidJson(): void
    {
        $connection = new Connection();

        $response = new Response(200, [], 'InvalidJson');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Json decode failed. Got: InvalidJson');

        $connection->parseResponse($response);
    }

    public function testParseResponseExtractsMeta(): void
    {
        $connection = new Connection();

        $responseBody = [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'from' => 1,
                'last_page' => 2,
                'path' => '/foo/bar',
                'per_page' => 25,
                'to' => 25,
                'total' => 27
            ],
        ];

        $response = new Response(200, [], json_encode($responseBody));

        $this->assertEquals([], $connection->parseResponse($response));
        $this->assertInstanceOf(Meta::class, $connection->meta);
    }

    public function testParseResponseUnwrapsData(): void
    {
        $connection = new Connection();

        $responseBody = [
            'data' => [
                'foo' => 'bar',
            ]
        ];

        $response = new Response(200, [], json_encode($responseBody));

        $this->assertEquals(['foo' => 'bar'], $connection->parseResponse($response));

        $responseBody = [
            'foo' => 'bar',
        ];

        $response = new Response(200, [], json_encode($responseBody));

        $this->assertEquals(['foo' => 'bar'], $connection->parseResponse($response));
    }

    public function testParseExceptionHandlesExceptions(): void
    {
        $exception = new \Exception('RandomException');

        $connection = new Connection();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('RandomException');

        $connection->parseException($exception);
    }

    public function testParseExceptionHandlesServerExceptions(): void
    {
        $exception = new ServerException('Server exception', new Request('GET', '/'), new Response(500));

        $connection = new Connection();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Server exception');

        $connection->parseException($exception);
    }

    public function testParseExceptionHandlesInvalidJson(): void
    {
        $exception = new ClientException('Foo', new Request('GET', '/'), new Response(422, [], 'InvalidJson'));

        $connection = new Connection();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Json decode failed. Got: InvalidJson');

        $connection->parseException($exception);
    }

    public function testParseExceptionHandlesErrorsMessages(): void
    {
        $exception = new ClientException(
            'Foo',
            new Request('GET', '/'),
            new Response(422, [], json_encode(['message' => 'Error message']))
        );

        $connection = new Connection();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Error message');

        $connection->parseException($exception);
    }

    public function testParseExceptionSetsErrors(): void
    {
        $exception = new ClientException(
            'Foo',
            new Request('GET', '/'),
            new Response(422, [], json_encode(['message' => 'Error message', 'errors' => ['First', 'Second']]))
        );

        $connection = new Connection();

        try {
            $connection->parseException($exception);
        } catch (ApiException $e) {
            $this->assertSame(['First', 'Second'], $e->getErrors());
        }

        $exception = new ClientException(
            'Foo',
            new Request('GET', '/'),
            new Response(422, [], json_encode(['message' => 'Error message']))
        );

        try {
            $connection->parseException($exception);
        } catch (ApiException $e) {
            $this->assertSame([], $e->getErrors());
        }
    }

    public function testTokensAreAcquiredWithAuthorizationCode(): void
    {
        $connection = new Connection();

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'FromAuthCode',
                'refresh_token' => 'RefreshToken',
                'expires_in' => 3600,
            ]))
        ]);

        $client = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $connection->setTransport($client);

        $connection->setClientId('clientId');
        $connection->setRedirectUrl('https://www.example.com/');
        $connection->setClientSecret('clientSecret');
        $connection->setAuthorizationCode('123456789');

        $connection->checkOrAcquireAccessToken();

        $this->assertEquals('FromAuthCode', $connection->getAccessToken());
        $this->assertEquals('RefreshToken', $connection->getRefreshToken());
        $this->assertEquals(time() + 3600, $connection->getTokenExpires());

        $this->assertEquals('https://app.sendy.nl/oauth/token', (string) $mockHandler->getLastRequest()->getUri());
    }

    public function testTokensAreAcquiredWithRefreshToken(): void
    {
        $connection = new Connection();

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'NewAccessToken',
                'refresh_token' => 'NewRefreshToken',
                'expires_in' => 3600,
            ]))
        ]);

        $client = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $connection->setTransport($client);

        $connection->setClientId('clientId');
        $connection->setClientSecret('clientSecret');
        $connection->setRefreshToken('RefreshToken');

        $connection->checkOrAcquireAccessToken();

        $this->assertEquals('NewAccessToken', $connection->getAccessToken());
        $this->assertEquals('NewRefreshToken', $connection->getRefreshToken());
        $this->assertEquals(time() + 3600, $connection->getTokenExpires());

        $this->assertEquals(
            'https://app.sendy.nl/oauth/token',
            (string) $mockHandler->getLastRequest()->getUri()
        );
    }

    public function testTokenUpdateCallbackIsCalled(): void
    {
        $connection = new Connection();

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'NewAccessToken',
                'refresh_token' => 'NewRefreshToken',
                'expires_in' => 3600,
            ]))
        ]);

        $client = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $connection->setTransport($client);

        $connection->setClientId('clientId');
        $connection->setClientSecret('clientSecret');
        $connection->setRefreshToken('RefreshToken');

        $connection->setTokenUpdateCallback(function (Connection $connection) {
            $this->assertEquals('NewAccessToken', $connection->getAccessToken());
            $this->assertEquals('NewRefreshToken', $connection->getRefreshToken());
            $this->assertEquals(time() + 3600, $connection->getTokenExpires());
        });

        $connection->checkOrAcquireAccessToken();
    }

    public function testGetRequestIsBuiltAndSent(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['foo' => 'bar'])),
            new Response(200, [], json_encode(['foo' => 'bar'])),
            new Response(500, [], 'Something went wrong'),
        ]);

        $client = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $connection->setTransport($client);

        $this->assertEquals(['foo' => 'bar'], $connection->get('/foo'));

        $this->assertEquals('/api/foo', (string) $mockHandler->getLastRequest()->getUri());
        $this->assertEquals('GET', $mockHandler->getLastRequest()->getMethod());

        $connection->get('/foo', ['baz' => 'foo']);

        $this->assertEquals('/api/foo?baz=foo', (string) $mockHandler->getLastRequest()->getUri());
        $this->assertEquals('GET', $mockHandler->getLastRequest()->getMethod());

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Something went wrong');
        $this->expectExceptionCode(500);

        $connection->get('/foo');
    }

    public function testDeleteRequestIsBuiltAndSent(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $mockHandler = new MockHandler([
            new Response(204, [], json_encode(['foo' => 'bar'])),
        ]);

        $client = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $connection->setTransport($client);

        $this->assertEquals([], $connection->delete('/bar'));

        $this->assertEquals('/api/bar', (string) $mockHandler->getLastRequest()->getUri());
        $this->assertEquals('DELETE', $mockHandler->getLastRequest()->getMethod());
    }

    public function testPostRequestIsBuiltAndSent(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $mockHandler = new MockHandler([
            new Response(201, [], json_encode(['foo' => 'bar'])),
        ]);

        $client = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $connection->setTransport($client);

        $this->assertEquals(['foo' => 'bar'], $connection->post('/foo', ['request' => 'body']));

        $this->assertEquals('/api/foo', (string) $mockHandler->getLastRequest()->getUri());
        $this->assertEquals('POST', $mockHandler->getLastRequest()->getMethod());
        $this->assertEquals('{"request":"body"}', $mockHandler->getLastRequest()->getBody()->getContents());
    }

    public function testPutRequestIsBuiltAndSent(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $mockHandler = new MockHandler([
            new Response(201, [], json_encode(['foo' => 'bar'])),
        ]);

        $client = new Client(['handler' => HandlerStack::create($mockHandler)]);

        $connection->setTransport($client);

        $this->assertEquals(['foo' => 'bar'], $connection->put('/foo', ['request' => 'body']));

        $this->assertEquals('/api/foo', (string) $mockHandler->getLastRequest()->getUri());
        $this->assertEquals('PUT', $mockHandler->getLastRequest()->getMethod());
        $this->assertEquals('{"request":"body"}', $mockHandler->getLastRequest()->getBody()->getContents());
    }
}
