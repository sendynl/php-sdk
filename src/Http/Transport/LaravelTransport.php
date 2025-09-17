<?php

namespace Sendy\Api\Http\Transport;

use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Sendy\Api\Exceptions\TransportException;
use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

/**
 * Example implementation for Laravel
 * @todo move to a separate package
 */
class LaravelTransport implements TransportInterface
{
    public function send(Request $request): Response
    {
        $headers = $request->getHeaders();
        $contentType = Arr::pull($headers, 'Content-Type', 'application/json');

        try {
            $response = Http::withHeaders($headers)
                ->withBody($request->getBody(), $contentType)
                ->withMethod($request->getMethod())
                ->withUrl($request->getUrl())
                ->send();
        } catch (\Throwable $e) {
            throw new TransportException($e->getMessage(), $e->getCode(), $e);
        }

        return new Response($response->status(), $response->headers(), $response->body());
    }

    public function getUserAgent(): string
    {
        return 'LaravelHttpClient/' . Application::VERSION;
    }
}
