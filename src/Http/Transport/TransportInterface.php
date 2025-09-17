<?php

namespace Sendy\Api\Http\Transport;

use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

interface TransportInterface
{
    /**
     * @throws \Sendy\Api\Exceptions\TransportException
     */
    public function send(Request $request): Response;

    /**
     * Get the part of the user agent string that identifies the HTTP client.
     */
    public function getUserAgent(): string;
}
