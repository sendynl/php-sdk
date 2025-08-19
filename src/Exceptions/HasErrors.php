<?php

namespace Sendy\Api\Exceptions;

trait HasErrors
{
    /** @var array<string, string[]> */
    private array $errors = [];

    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param string[][] $errors
     */
    final public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $errors = []
    ) {
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
