<?php

namespace Sendy\Api\Http\Transport;

use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

interface TransportInterface
{
    /**
     * @param Request $request
     *
     * @throws \Sendy\Api\Exceptions\TransportException
     */
    public function send(Request $request): Response;

    public function getUserAgent(): string;
}
