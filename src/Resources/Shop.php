<?php

namespace Sendy\Api\Resources;

use GuzzleHttp\Exception\GuzzleException;
use Sendy\Api\ApiException;

class Shop extends Resource
{
    /**
     * List all shops
     *
     * Display all shops in a list
     *
     * @see https://app.sendy.nl/api/docs#tag/Shops/operation/getShops
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
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
     * @see https://app.sendy.nl/api/docs#tag/Shops/operation/getShopByUuid
     * @param string $id
     * @return array<string, mixed|array<string|mixed>>
     * @throws ApiException
     * @throws GuzzleException
     */
    public function get(string $id): array
    {
        return $this->connection->get('/shops/' . $id);
    }
}
