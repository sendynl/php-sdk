<?php

namespace Sendy\Api\Resources;

use Sendy\Api\Exceptions\SendyException;

final class Carrier extends Resource
{
    /**
     * List all carriers
     *
     * Display all carriers in a list.
     *
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     * @link https://app.sendy.nl/api/docs#tag/Carriers/operation/api.carriers.index
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
     * @throws SendyException
     * @link https://app.sendy.nl/api/docs#tag/Carriers/operation/api.carriers.show
     */
    public function get(int $id): array
    {
        return $this->connection->get('/carriers/' . $id);
    }
}
