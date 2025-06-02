<?php

namespace Sendy\Api\Resources;

use Sendy\Api\Exceptions\SendyException;

class Shop extends Resource
{
    /**
     * List all shops
     *
     * Display all shops in a list
     *
     * @link https://app.sendy.nl/api/docs#tag/Shops/operation/api.shops.index
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function list(): array
    {
        return $this->connection->get('/shops');
    }

    /**
     * Get a shop
     *
     * Get a specific shop by its UUID
     *
     * @link https://app.sendy.nl/api/docs#tag/Shops/operation/api.shops.show
     * @param string $id
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function get(string $id): array
    {
        return $this->connection->get('/shops/' . $id);
    }
}
