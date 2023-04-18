<?php

namespace Sendy\Api\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Sendy\Api\Connection;

trait TestsEndpoints
{
    public function buildConnectionWithMockHandler(MockHandler $handler): Connection
    {
        $connection = new Connection();
        $connection->setAccessToken('PersonalAccessToken');

        $client = new Client(['handler' => HandlerStack::create($handler)]);

        $connection->setClient($client);

        return $connection;
    }
}
