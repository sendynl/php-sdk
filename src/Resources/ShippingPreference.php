<?php

namespace Sendy\Api\Resources;

use Sendy\Api\Exceptions\SendyException;

class ShippingPreference extends Resource
{
    /**
     * List all shipping preferences
     *
     * Display all active shipping preferences for the active company in a list.
     *
     * @link https://app.sendy.nl/api/docs#tag/Shipping-preferences
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function list(): array
    {
        return $this->connection->get('/shipping_preferences');
    }
}
