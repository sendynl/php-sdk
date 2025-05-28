<?php

namespace Sendy\Api;

/**
 * @deprecated This class exists for backwards compatibility and may be removed in a future version.
 * @todo Replace internal usages with the new more granular exceptions in `Sendy\Api\Exceptions`.
 * @internal
 */
class ApiException extends \Exception
{
    /** @var array<string, string[]> */
    private array $errors = [];

    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param string[][] $errors
     */
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, array $errors = [])
    {
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
