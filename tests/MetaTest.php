<?php

namespace Sendy\Api\Tests;

use Sendy\Api\Meta;
use PHPUnit\Framework\TestCase;

class MetaTest extends TestCase
{
    public function testBuildFromResponseBuildsMetaObject(): void
    {
        $response = [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 2,
            'path' => '/foo/bar',
            'per_page' => 25,
            'to' => 25,
            'total' => 27,
        ];

        $this->assertInstanceOf(Meta::class, Meta::buildFromResponse($response));
        $this->assertEquals(1, Meta::buildFromResponse($response)->currentPage);
        $this->assertEquals(1, Meta::buildFromResponse($response)->from);
        $this->assertEquals(2, Meta::buildFromResponse($response)->lastPage);
        $this->assertEquals('/foo/bar', Meta::buildFromResponse($response)->path);
        $this->assertEquals(25, Meta::buildFromResponse($response)->perPage);
        $this->assertEquals(27, Meta::buildFromResponse($response)->total);
    }
}
