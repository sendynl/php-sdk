<?php

namespace Sendy\Api\Resources;

final class Me extends Resource
{
    /**
     * Get your user profile
     *
     * Display the currently authenticated user’s profile.
     *
     * @see https://app.sendy.nl/api/docs#tag/User/operation/getProfileInformation
     * @return array<string, mixed|array<string|mixed>>
     */
    public function get(): array
    {
        return $this->connection->get('/me');
    }
}
