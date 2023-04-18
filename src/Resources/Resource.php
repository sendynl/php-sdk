<?php

namespace Sendy\Api\Resources;

use Sendy\Api\Connection;

abstract class Resource
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
}
