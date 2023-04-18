<?php

namespace Sendy\Api\Resources;

use GuzzleHttp\Exception\GuzzleException;
use Sendy\Api\ApiException;

class ShippingPreference extends Resource
{
    /**
     * List all shipping preferences
     *
     * Display all active shipping preferences for the active company in a list.
     *
     * @see https://app.sendy.nl/api/docs#tag/Shipping-preferences/operation/shipping_preferences.index
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     */
    public function list(): array
    {
        return $this->connection->get('/shipping_preferences');
    }
}
