<?php

namespace Sendy\Api\Resources;

use Sendy\Api\Exceptions\SendyException;

class Service extends Resource
{
    /**
     * List services associated with a carrier
     *
     * Display all services associated with a carrier in a list.
     *
     * @param int $carrierId The id of the carrier
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     * @link https://app.sendy.nl/api/docs#tag/Services/operation/api.carriers.services.index
     */
    public function list(int $carrierId): array
    {
        return $this->connection->get("/carriers/{$carrierId}/services");
    }
}
