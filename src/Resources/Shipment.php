<?php

namespace Sendy\Api\Resources;

use GuzzleHttp\Exception\GuzzleException;
use Sendy\Api\ApiException;
use Sendy\Api\Meta;

final class Shipment extends Resource
{
    /**
     * List all shipments
     *
     * Display all shipments in a paginated list.
     *
     * @param int $page The page number to fetch
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     * @link https://app.sendy.nl/api/docs#tag/Shipments/operation/api.shipments.index
     * @see Meta
     */
    public function list(int $page = 1): array
    {
        return $this->connection->get("/shipments", ['page' => $page]);
    }

    /**
     * Get a shipment
     *
     * Get a specific shipment by its UUID.
     *
     * @param string $id The UUID of the shipment
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     * @link https://app.sendy.nl/api/docs#tag/Shipments/operation/api.shipments.show
     */
    public function get(string $id): array
    {
        return $this->connection->get("/shipments/{$id}");
    }

    /**
     * Update a shipment
     *
     * Update an existing shipment.
     *
     * @param string $id The UUID of the shipment
     * @param array<string, mixed|array<string,mixed>> $data
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     */
    public function update(string $id, array $data): array
    {
        return $this->connection->put("/shipments/{$id}", $data);
    }

    /**
     * Cancel or delete a shipment
     *
     * Depending on the shipment’s status, cancel or delete the shipment.
     *
     * When the status of the shipment is `new`, it will be deleted. When the shipment has been `generated`, it will be
     * cancelled. When the shipment has a status that does not allow deleting or cancelling, the API will return a 422
     * response.
     *
     * @param string $id The UUID of the shipment
     * @return array<string, mixed|array<string|mixed>>
     * @throws GuzzleException
     * @throws ApiException
     * @link https://app.sendy.nl/api/docs#tag/Shipments/operation/api.shipments.destroy
     */
    public function delete(string $id): array
    {
        return $this->connection->delete("/shipments/{$id}");
    }

    /**
     * Create a shipment from preference
     *
     * Create a new shipment from preference.
     *
     * @param array<string, mixed|array<string,mixed>> $data
     * @param bool $generateDirectly Should the shipment be generated right away. This will increase the response time.
     * @return array<string, mixed|array<string|mixed>>
     * @throws ApiException
     * @throws GuzzleException
     * @link https://app.sendy.nl/api/docs#tag/Shipments/operation/api.shipments.preference
     * @see ShippingPreference
     */
    public function createFromPreference(array $data, bool $generateDirectly = true): array
    {
        return $this->connection->post("/shipments/preference", $data, ['generateDirectly' => $generateDirectly]);
    }

    /**
     * Create a shipment from a smart rule
     *
     * @param array<string, mixed|array<string,mixed>> $data
     * @return array<string, mixed|array<string|mixed>>
     * @throws ApiException
     * @throws GuzzleException
     * @link https://app.sendy.nl/api/docs#tag/Shipments/operation/api.shipments.smart-rule
     */
    public function createWithSmartRules(array $data): array
    {
        return $this->connection->post('/shipments/smart-rule', $data);
    }

    /**
     * Generate a shipment
     *
     * Generate a shipping label for an existing shipment
     *
     * @param string $id The UUID of the shipment
     * @param bool $asynchronous Whether the shipping label should be generated asynchronously
     * @return array<string, mixed|array<string|mixed>>
     * @throws ApiException
     * @throws GuzzleException
     * @link https://app.sendy.nl/api/docs#tag/Shipments/operation/api.shipments.generate
     */
    public function generate(string $id, bool $asynchronous = true): array
    {
        return $this->connection->post("/shipments/{$id}/generate", ['asynchronous' => $asynchronous]);
    }

    /**
     * Get labels for a shipment
     *
     * Get a PDF with the shipping labels for all of a shipment’s packages
     *
     * @param string $id The UUID of the shipment
     * @return array<string, mixed|array<string|mixed>>
     * @throws ApiException
     * @throws GuzzleException
     * @link https://app.sendy.nl/api/docs#tag/Documents/operation/api.shipments.labels.index
     */
    public function labels(string $id): array
    {
        return $this->connection->get("/shipments/{$id}/labels");
    }

    /**
     * Get export documents for a shipment
     *
     * Get a PDF with the export documents for a specific shipment
     *
     * @param string $id
     * @return array<string, mixed|array<string|mixed>>
     * @throws ApiException
     * @throws GuzzleException
     * @link https://app.sendy.nl/api/docs#tag/Documents/operation/api.shipments.documents.index
     */
    public function documents(string $id): array
    {
        return $this->connection->get("/shipments/{$id}/documents");
    }
}
