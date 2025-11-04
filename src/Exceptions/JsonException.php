<?php

namespace Sendy\Api\Exceptions;

class JsonException extends \JsonException implements SendyException
{
    /**
     * @internal This exists for backwards compatibility with the ApiException interface.
     */
    public function getErrors(): array
    {
        return [];
    }
}
