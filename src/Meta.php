<?php

namespace Sendy\Api;

final class Meta
{
    public int $currentPage;

    public int $from;

    public int $lastPage;

    public string $path;

    public int $perPage;

    public int $to;

    public int $total;

    public function __construct(
        int $currentPage,
        int $from,
        int $lastPage,
        string $path,
        int $perPage,
        int $to,
        int $total
    ) {
        $this->currentPage = $currentPage;
        $this->from = $from;
        $this->lastPage = $lastPage;
        $this->path = $path;
        $this->perPage = $perPage;
        $this->to = $to;
        $this->total = $total;
    }

    /**
     * @param array<string, int|string> $meta
     */
    public static function buildFromResponse(array $meta): Meta
    {
        return new self(
            $meta['current_page'],
            $meta['from'],
            $meta['last_page'],
            $meta['path'],
            $meta['per_page'],
            $meta['to'],
            $meta['total'],
        );
    }
}
