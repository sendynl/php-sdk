<?php

namespace Sendy\Api\Resources;

use GuzzleHttp\Exception\GuzzleException;
use Sendy\Api\ApiException;

class Parcelshop extends Resource
{
    /**
     * List parcel shops
     *
     * Fetch a list of parcel shops near a given geo-location.
     *
     * @param array<string> $carriers Carriers to fetch the parcel shops for.
     * @param float $latitude The latitude of the location.
     * @param float $longitude The longitude of the location.
     * @param string $country The country code of the location.
     * @param string|null $postalCode The postal code of the location.
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     * @link https://app.sendy.nl/api/docs#tag/Parcel-shops
     */
    public function list(
        array $carriers,
        float $latitude,
        float $longitude,
        string $country,
        string $postalCode = null
    ): array {
        $params = [
            'carriers' => $carriers,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'country' => $country,
            'postal_code' => $postalCode,
        ];

        return $this->connection->get('/parcel_shops', $params);
    }
}
