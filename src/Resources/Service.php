<?php

namespace Sendy\Api\Resources;

use GuzzleHttp\Exception\GuzzleException;
use Sendy\Api\ApiException;

class Service extends Resource
{
    /**
     * List services associated with a carrier
     *
     * Display all services associated with a carrier in a list.
     *
     * @param int $carrierId The id of the carrier
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     * @see https://app.sendy.nl/api/docs#tag/Services/operation/getCarrierServices
     */
    public function list(int $carrierId): array
    {
        return $this->connection->get("/carriers/{$carrierId}/services");
    }
}
