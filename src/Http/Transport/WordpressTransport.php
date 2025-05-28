<?php

namespace Sendy\Api\Http\Transport;

use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

/**
 * Example implementation for WordPress
 * @todo move this to the wordpress plugin
 */
class WordpressTransport implements TransportInterface
{
    public function send(Request $request): Response
    {
        $args = [
            'method' => $request->getMethod(),
            'headers' => $request->getHeaders(),
            'body' => $request->getBody(),
        ];

        if ($request->getMethod() === 'GET') {
            unset($args['body']);
        }

        $response = wp_remote_request($request->getUrl(), $args);

        if (is_wp_error($response)) {
            throw new \Sendy\Api\Exceptions\TransportException($response->get_error_message());
        }

        return new Response(
            wp_remote_retrieve_response_code($response),
            wp_remote_retrieve_headers($response),
            wp_remote_retrieve_body($response)
        );
    }

    public function getUserAgent(): string
    {
        return 'WP_Http/' . get_bloginfo('version');
    }
}
