<?php

namespace Sendy\Api\Exceptions;

trait HasErrors
{
    /** @var array<string, list<string>> */
    private array $errors = [];

    /**
     * @param array<string, list<string>> $errors
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
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
