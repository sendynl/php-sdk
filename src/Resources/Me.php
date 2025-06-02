<?php

namespace Sendy\Api\Resources;

use Sendy\Api\Exceptions\SendyException;

final class Me extends Resource
{
    /**
     * Get your user profile
     *
     * Display the currently authenticated user’s profile.
     *
     * @link https://app.sendy.nl/api/docs#tag/User/operation/api.me
     * @return array<string, mixed|array<string|mixed>>
     * @throws SendyException
     */
    public function get(): array
    {
        return $this->connection->get('/me');
    }
}
