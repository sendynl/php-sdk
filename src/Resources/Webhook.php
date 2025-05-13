<?php

namespace Sendy\Api\Resources;

final class Webhook extends Resource
{
    /**
     * List all webhooks
     *
     * @return array<string, mixed|array<string|mixed>>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sendy\Api\ApiException
     * @link https://app.sendy.nl/api/docs#tag/Webhooks/operation/api.webhooks.index
     */
    public function list(): array
    {
        return $this->connection->get('/webhooks');
    }

    /**
     * Create a new webhook
     *
     * @param array<string, mixed|array<string|mixed>> $data
     * @return array<string, mixed|array<string|mixed>>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sendy\Api\ApiException
     * @link https://app.sendy.nl/api/docs#tag/Webhooks/operation/api.webhooks.store
     */
    public function create(array $data): array
    {
        return $this->connection->post('/webhooks', $data);
    }

    /**
     * Delete a webhook
     *
     * @param string $id The ID of the webhook
     * @return array<empty>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sendy\Api\ApiException
     */
    public function delete(string $id): array
    {
        return $this->connection->delete("/webhooks/{$id}");
    }

    /**
     * Update an existing webhook
     *
     * @param string $id The id of the webhook to be updated
     * @param array<string, mixed|array<string|mixed>> $data
     * @return array<string, mixed|array<string|mixed>>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sendy\Api\ApiException
     */
    public function update(string $id, array $data): array
    {
        return $this->connection->put("/webhooks/{$id}", $data);
    }
}
