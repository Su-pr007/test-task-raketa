<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Infrastructure;

use Redis;
use RedisException;

class ConnectorFacade // Если всегда происходит подключение к определённому сервису, то это уже не фасад
{
    public ?Connector $connector;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly ?string $password = null,
        private readonly ?int $dbIndex = null,
    ) {}

    protected function build(): void
    {
        $redis = new Redis();

        $isConnected = $redis->isConnected();
        if (!$isConnected && $redis->ping('Pong')) {
            $isConnected = $redis->connect(
                $this->host,
                $this->port,
            );
        }

        if ($isConnected) {
            $redis->auth($this->password);
            $redis->select($this->dbIndex);
            $this->connector = new Connector($redis);
        }
    }
}
