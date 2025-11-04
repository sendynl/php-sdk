<?php

namespace Sendy\Api\Tests;

use Sendy\Api\Connection;
use Sendy\Api\Http\Transport\MockTransport;

trait TestsEndpoints
{
    public function buildConnectionWithMockTransport(MockTransport $transport): Connection
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');
        $connection->setTransport($transport);

        return $connection;
    }
}
