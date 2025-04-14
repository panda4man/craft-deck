<?php

namespace App\Services;

use xPaw\MinecraftQuery;

class ServerQueryService
{
    protected MinecraftQuery $query;

    public function __construct()
    {
        $this->query = new MinecraftQuery();
    }

    public function query(string $host, int $port = 25565)
    {
        try {
            $this->query->Connect($host, $port, 2);
            return $this->query->GetInfo();
        } catch (\Exception $e) {
            return null;
        }
    }
}
