<?php

namespace Sendy\Api\Exceptions;

use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

abstract class HttpException extends \Exception implements SendyException
{
    use HasErrors;

    /**
     * The request that caused the HTTP error.
     */
    private Request $request;

    /**
     * The response that describes the HTTP error.
     */
    private Response $response;

    public static function fromRequestAndResponse(Request $request, Response $response, ?string $message = null): self
    {
        $exception = new static(
            $message ?? $response->getSummary(),
            $response->getStatusCode(),
            null,
            $response->getErrors(),
        );

        $exception->request = $request;
        $exception->response = $response;

        return $exception;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
