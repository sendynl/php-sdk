<?php

namespace Sendy\Api\Resources;

use Sendy\Api\Exceptions\SendyException;

final class Label extends Resource
{
    /**
     * Get labels for multiple shipments
     *
     * Get a combined PDF with labels of all packages in the given shipments
     *
     * @param non-empty-array<string> $shipmentIds The ids of the shipments
     * @param null|'A4'|'A6' $paperType The paper size to combine the labels on
     * @param null|'top-left'|'top-right'|'bottom-left'|'bottom-right' $startLocation Where to start combining the
     *  labels. Only used when $paperType is set to A4.
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     * @link https://app.sendy.nl/api/docs#tag/Documents/operation/api.labels.index
     */
    public function get(array $shipmentIds, ?string $paperType = null, ?string $startLocation = null): array
    {
        $params = [
            'ids' => $shipmentIds,
            'paper_type' => $paperType,
            'start_location' => $startLocation,
        ];

        return $this->connection->get('/labels', $params);
    }
}
