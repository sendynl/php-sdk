<?php

namespace Sendy\Api\Tests;

use PHPUnit\Framework\TestCase;
use Sendy\Api\ApiException;
use Sendy\Api\Exceptions\ServerException;
use Sendy\Api\Exceptions\ValidationException;
use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

class ResponseTest extends TestCase
{
    public function testToExceptionReturnsServerException(): void
    {
        $response = new Response(500, [], '');

        $exception = $response->toException(new Request('GET', '/foo'));

        $this->assertInstanceOf(ServerException::class, $exception);
        $this->assertSame(500, $exception->getCode());
        $this->assertSame('500 - Internal Server Error', $exception->getMessage());
    }

    public function testToExceptionHandlesInvalidJson(): void
    {
        $response = new Response(422, [], 'InvalidJson');

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Json decode failed. Got: InvalidJson');

        $response->toException(new Request('GET', '/foo'));
    }

    public function testToExceptionHandlesValidationMessages(): void
    {
        $response = new Response(422, [], json_encode(['message' => 'Error message']));

        $exception = $response->toException(new Request('GET', '/foo'));

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertSame(422, $exception->getCode());
        $this->assertSame('Error message', $exception->getMessage());
    }

    public function testToExceptionSetsErrors(): void
    {
        $response = new Response(422, [], json_encode(['message' => 'Error message', 'errors' => ['First', 'Second']]));
        $this->assertSame(['First', 'Second'], $response->toException(new Request('GET', '/foo'))->getErrors());

        $response = new Response(422, [], json_encode(['message' => 'Error message']));
        $this->assertSame([], $response->toException(new Request('GET', '/foo'))->getErrors());
    }
}
