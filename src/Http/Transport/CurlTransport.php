<?php

namespace Sendy\Api\Http\Transport;

use Sendy\Api\Exceptions\TransportException;
use Sendy\Api\Http\Request;
use Sendy\Api\Http\Response;

class CurlTransport implements TransportInterface
{
    public function send(Request $request): Response
    {
        if (!extension_loaded('curl')) {
            throw new TransportException('cURL PHP extension is not loaded.');
        }

        $curlHandle = curl_init();

        try {
            curl_setopt($curlHandle, CURLOPT_URL, $request->getUrl());
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $request->getMethod());
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_HEADER, true);
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $this->formatHeaders($request->getHeaders()));

            if ($body = $request->getBody()) {
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
            }

            $response = curl_exec($curlHandle);

            if ($response === false) {
                $error = curl_error($curlHandle);

                throw new TransportException('cURL error: ' . $error);
            }

            $headerSize = curl_getinfo($curlHandle, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            $statusCode = curl_getinfo($curlHandle, CURLINFO_RESPONSE_CODE);

            return new Response($statusCode, $this->parseHeaders($headers), $body);
        } finally {
            curl_close($curlHandle);
        }
    }

    public function getUserAgent(): string
    {
        if (!extension_loaded('curl')) {
            return 'curl';
        }

        return 'curl/' . curl_version()['version'];
    }

    /**
     * Formats headers for cURL.
     *
     * @param array<string, string> $headers
     * @return list<string>
     */
    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $name => $value) {
            $formatted[] = $name . ': ' . $value;
        }
        return $formatted;
    }

    /**
     * Parses the raw header string into an associative array.
     *
     * @param string $rawHeaders
     *
     * @return array<string, list<string>>
     */
    private function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        $lines = explode("\r\n", $rawHeaders);

        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$name, $value] = explode(': ', $line, 2);
                $headers[strtolower($name)][] = $value;
            }
        }

        return $headers;
    }
}
