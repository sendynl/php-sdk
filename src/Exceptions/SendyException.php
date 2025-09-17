<?php

namespace Sendy\Api\Exceptions;

use Sendy\Api\ApiException;

interface SendyException extends ApiException
{
    /**
     * Get the error details from the response.
     *
     * @return array<string, list<string>>
     */
    public function getErrors(): array;
}
