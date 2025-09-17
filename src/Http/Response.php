<?php

namespace Sendy\Api\Http;

use Sendy\Api\ApiException;
use Sendy\Api\Exceptions\ClientException;
use Sendy\Api\Exceptions\HttpException;
use Sendy\Api\Exceptions\JsonException;
use Sendy\Api\Exceptions\ServerException;
use Sendy\Api\Exceptions\ValidationException;

final class Response
{
    private const PHRASES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    private int $statusCode;

    /**
     * @var array<string, list<string>>
     */
    private array $headers;

    private string $body;

    /**
     * @param array<string, list<string>|string> $headers
     */
    public function __construct(int $statusCode, array $headers, string $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = array_map(
            fn($value) => is_array($value) ? $value : [$value],
            array_change_key_case($headers, CASE_LOWER),
        );
        $this->body = $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Decode the JSON body of the response.
     *
     * @return array<string, mixed>
     * @throws JsonException If the body is not valid JSON.
     */
    public function getDecodedBody(): array
    {
        try {
            return json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new JsonException("Json decode failed. Got: {$this->body}", $this->statusCode, $e);
        }
    }

    /**
     * Get a summary of the response, suitable for use in exception messages.
     */
    public function getSummary(): string
    {
        $summary = $this->statusCode . ' - ' . (self::PHRASES[$this->statusCode] ?? 'Unknown Status');
        $decodedBody = json_decode($this->body, true);

        if (isset($decodedBody['error_description'], $decodedBody['hint'])) {
            $summary .= ": {$decodedBody['error_description']} ({$decodedBody['hint']})";
        } elseif (isset($decodedBody['message'])) {
            $summary .= ": {$decodedBody['message']}";
        }

        return $summary;
    }

    /**
     * Extract errors from the response body.
     *
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        $data = json_decode($this->body, true);

        return $data['errors'] ?? [];
    }

    public function toException(Request $request): ?HttpException
    {
        if ($this->statusCode === 422) {
            return ValidationException::fromRequestAndResponse(
                $request,
                $this,
                $this->getDecodedBody()['message'] ?? 'Validation failed',
            );
        }

        if ($this->statusCode >= 400 && $this->statusCode < 500) {
            return ClientException::fromRequestAndResponse($request, $this);
        }

        if ($this->statusCode >= 500) {
            return ServerException::fromRequestAndResponse($request, $this);
        }

        return null;
    }
}
