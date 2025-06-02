<?php

namespace Sendy\Api\Exceptions;

/**
 * Represents an HTTP 5xx error that occurred during a request to the Sendy API.
 */
class ServerException extends \Exception implements SendyException
{
    use HasErrors;
}
