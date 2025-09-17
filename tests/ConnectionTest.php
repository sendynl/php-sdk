<?php

namespace Sendy\Api\Tests;

use PHPUnit\Framework\TestCase;
use Sendy\Api\ApiException;
use Sendy\Api\Connection;
use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;
use Sendy\Api\Http\Transport\MockTransport;
use Sendy\Api\Http\Transport\TransportFactory;
use Sendy\Api\Meta;
use Sendy\Api\Resources\Me;

class ConnectionTest extends TestCase
{
    public function testUserAgentIsSet(): void
    {
        $connection = $this->createConnection();
        $this->assertEquals(
            sprintf('SendySDK/3.0.0 PHP/%s GuzzleHttp/7', phpversion()),
            $connection->createRequest('GET', '/')->getHeaders()['user-agent'],
        );

        $connection = $this->createConnection();
        $connection->setUserAgentAppendix('WooCommerce/6.2');
        $this->assertEquals(
            sprintf('SendySDK/3.0.0 PHP/%s GuzzleHttp/7 WooCommerce/6.2', phpversion()),
            $connection->createRequest('GET', '/')->getHeaders()['user-agent'],
        );

        $connection = $this->createConnection();
        $connection->setOauthClient(true);
        $this->assertEquals(
            sprintf('SendySDK/3.0.0 PHP/%s OAuth/2.0 GuzzleHttp/7', phpversion()),
            $connection->createRequest('GET', '/')->getHeaders()['user-agent'],
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
            $connection->getAuthorizationUrl(),
        );
        // phpcs:enable
    }

    public function testParseResponseReturnsEmptyArrayWhenResponseHasNoContent(): void
    {
        $connection = new Connection();

        $response = new Response(204, [], '');

        $this->assertEquals([], $connection->parseResponse($response, new Request('GET', '/foo')));
    }

    public function testParseResponseThrowsApiExceptionWithInvalidJson(): void
    {
        $connection = new Connection();

        $response = new Response(200, [], 'InvalidJson');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Json decode failed. Got: InvalidJson');

        $connection->parseResponse($response, new Request('GET', '/foo'));
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
                'total' => 27,
            ],
        ];

        $response = new Response(200, [], json_encode($responseBody));

        $this->assertEquals([], $connection->parseResponse($response, new Request('GET', '/foo')));
        $this->assertInstanceOf(Meta::class, $connection->meta);
    }

    public function testParseResponseUnwrapsData(): void
    {
        $connection = new Connection();

        $responseBody = [
            'data' => [
                'foo' => 'bar',
            ],
        ];

        $response = new Response(200, [], json_encode($responseBody));

        $this->assertEquals(['foo' => 'bar'], $connection->parseResponse($response, new Request('GET', '/foo')));

        $responseBody = [
            'foo' => 'bar',
        ];

        $response = new Response(200, [], json_encode($responseBody));

        $this->assertEquals(['foo' => 'bar'], $connection->parseResponse($response, new Request('GET', '/foo')));
    }

    public function testTokensAreAcquiredWithAuthorizationCode(): void
    {
        $connection = new Connection();

        $transport = new MockTransport(
            new Response(200, [], json_encode([
                'access_token' => 'FromAuthCode',
                'refresh_token' => 'RefreshToken',
                'expires_in' => 3600,
            ])),
        );

        $connection->setTransport($transport);

        $connection->setClientId('clientId');
        $connection->setRedirectUrl('https://www.example.com/');
        $connection->setClientSecret('clientSecret');
        $connection->setAuthorizationCode('123456789');

        $connection->checkOrAcquireAccessToken();

        $this->assertEquals('FromAuthCode', $connection->getAccessToken());
        $this->assertEquals('RefreshToken', $connection->getRefreshToken());
        $this->assertEquals(time() + 3600, $connection->getTokenExpires());

        $this->assertEquals('https://app.sendy.nl/oauth/token', $transport->getLastRequest()->getUrl());
    }

    public function testTokensAreAcquiredWithRefreshToken(): void
    {
        $connection = new Connection();

        $transport = new MockTransport(
            new Response(200, [], json_encode([
                'access_token' => 'NewAccessToken',
                'refresh_token' => 'NewRefreshToken',
                'expires_in' => 3600,
            ])),
        );

        $connection->setTransport($transport);

        $connection->setClientId('clientId');
        $connection->setClientSecret('clientSecret');
        $connection->setRefreshToken('RefreshToken');

        $connection->checkOrAcquireAccessToken();

        $this->assertEquals('NewAccessToken', $connection->getAccessToken());
        $this->assertEquals('NewRefreshToken', $connection->getRefreshToken());
        $this->assertEquals(time() + 3600, $connection->getTokenExpires());

        $this->assertEquals(
            'https://app.sendy.nl/oauth/token',
            $transport->getLastRequest()->getUrl(),
        );
    }

    public function testTokenUpdateCallbackIsCalled(): void
    {
        $connection = new Connection();

        $transport = new MockTransport(
            new Response(200, [], json_encode([
                'access_token' => 'NewAccessToken',
                'refresh_token' => 'NewRefreshToken',
                'expires_in' => 3600,
            ])),
        );

        $connection->setTransport($transport);

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

        $transport = new MockTransport(
            new Response(200, [], json_encode(['foo' => 'bar'])),
        );

        $connection->setTransport($transport);

        $this->assertEquals(['foo' => 'bar'], $connection->get('/foo'));
        $this->assertEquals('https://app.sendy.nl/api/foo', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }

    public function testGetRequestWithQueryParametersIsBuiltAndSent(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $transport = new MockTransport(
            new Response(200, [], json_encode(['foo' => 'bar'])),
        );

        $connection->setTransport($transport);

        $this->assertEquals(['foo' => 'bar'], $connection->get('/foo', ['baz' => 'foo']));
        $this->assertEquals('https://app.sendy.nl/api/foo?baz=foo', $transport->getLastRequest()->getUrl());
        $this->assertEquals('GET', $transport->getLastRequest()->getMethod());
    }

    public function testGetRequestWith4xxResponseThrowsClientException(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $transport = new MockTransport(
            new Response(418, [], '{}'),
        );

        $connection->setTransport($transport);

        $this->expectException(\Sendy\Api\Exceptions\ClientException::class);
        $this->expectExceptionMessage('418 - I\'m a teapot');
        $this->expectExceptionCode(418);
        $connection->get('/brew-coffee');
    }

    public function testGetRequestWith5xxResponseThrowsServerException(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $transport = new MockTransport(
            new Response(500, [], '{"message": "Something went wrong"}'),
        );

        $connection->setTransport($transport);

        $this->expectException(\Sendy\Api\Exceptions\ServerException::class);
        $this->expectExceptionMessage('500 - Internal Server Error: Something went wrong');
        $this->expectExceptionCode(500);

        $connection->get('/foo');
    }

    public function testDeleteRequestIsBuiltAndSent(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $transport = new MockTransport(
            new Response(204, [], json_encode(['foo' => 'bar'])),
        );

        $connection->setTransport($transport);

        $this->assertEquals([], $connection->delete('/bar'));

        $this->assertEquals('https://app.sendy.nl/api/bar', $transport->getLastRequest()->getUrl());
        $this->assertEquals('DELETE', $transport->getLastRequest()->getMethod());
    }

    public function testPostRequestIsBuiltAndSent(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $transport = new MockTransport(
            new Response(201, [], json_encode(['foo' => 'bar'])),
        );

        $connection->setTransport($transport);

        $this->assertEquals(['foo' => 'bar'], $connection->post('/foo', ['request' => 'body']));

        $this->assertEquals('https://app.sendy.nl/api/foo', $transport->getLastRequest()->getUrl());
        $this->assertEquals('POST', $transport->getLastRequest()->getMethod());
        $this->assertEquals('{"request":"body"}', $transport->getLastRequest()->getBody());
    }

    public function testPutRequestIsBuiltAndSent(): void
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $mockTransport = new MockTransport(
            new Response(201, [], json_encode(['foo' => 'bar'])),
        );

        $connection->setTransport($mockTransport);

        $this->assertEquals(['foo' => 'bar'], $connection->put('/foo', ['request' => 'body']));

        $this->assertEquals('https://app.sendy.nl/api/foo', $mockTransport->getLastRequest()->getUrl());
        $this->assertEquals('PUT', $mockTransport->getLastRequest()->getMethod());
        $this->assertEquals('{"request":"body"}', $mockTransport->getLastRequest()->getBody());
    }

    private function createConnection(): Connection
    {
        $connection = new Connection();
        $connection->setTransport(TransportFactory::createGuzzleTransport());

        return $connection;
    }
}
