<?php

namespace Sendy\Api\Resources;

use GuzzleHttp\Exception\GuzzleException;
use Sendy\Api\ApiException;

final class Carrier extends Resource
{
    /**
     * List all carriers
     *
     * Display all carriers in a list.
     *
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     * @see https://app.sendy.nl/api/docs#tag/Carriers/operation/getCarriers
     */
    public function list(): array
    {
        return $this->connection->get('/carriers');
    }

    /**
     * Get a carrier
     *
     * Get a specific carrier by its ID.
     *
     * @param int $id The id of the carrier
     * @return array<string, mixed|array<string|mixed>>
     * @throws ApiException
     * @throws GuzzleException
     * @see https://app.sendy.nl/api/docs#tag/Carriers/operation/getCarrier
     */
    public function get(int $id): array
    {
        return $this->connection->get('/carriers/' . $id);
    }
}
