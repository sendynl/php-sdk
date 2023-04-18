<?php

namespace Sendy\Api\Resources;

use GuzzleHttp\Exception\GuzzleException;
use Sendy\Api\ApiException;

final class Label extends Resource
{
    /**
     * Get labels for multiple shipments
     *
     * Get a combined PDF with labels of all packages in the given shipments
     *
     * @param non-empty-array<string> $shipmentIds The ids of the shipments
     * @param 'A4'|'A6' $paperType The paper size to combine the labels on
     * @param 'top-left'|'top-right'|'bottom-left'|'bottom-right' $startLocation Where to start combining the labels.
     *                                                                           Used when $paperType is set to A4.
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     * @see https://app.sendy.nl/api/docs#tag/Documents/operation/getLabels
     */
    public function get(array $shipmentIds, string $paperType = 'A6', string $startLocation = 'top-left'): array
    {
        $params = [
            'ids' => $shipmentIds,
            'paper_type' => $paperType,
            'start_location' => $startLocation,
        ];

        return $this->connection->get('/labels', $params);
    }
}
