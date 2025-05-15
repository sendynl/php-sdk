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
     * @link https://app.sendy.nl/api/docs#tag/Shops/operation/api.shops.index
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
     * @link https://app.sendy.nl/api/docs#tag/Shops/operation/api.shops.show
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
