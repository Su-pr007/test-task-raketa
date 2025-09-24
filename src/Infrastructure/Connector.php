<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Infrastructure;

use Raketa\BackendTestTask\Domain\Cart;
use Redis;
use RedisException;

class Connector
{
    public function __construct(private readonly Redis $redis) {}

    /**
     * @throws ConnectorException
     */
    public function get(string $key)
    {
        try {
            return unserialize($this->redis->get($key));
        } catch (RedisException $e) {
            throw new ConnectorException('Connector error', $e->getCode(), $e);
        }
    }

    /**
     * @throws ConnectorException
     */
    public function set(
        string $key,
        Cart $value,
        int $expiresInSeconds = 24 * 60 * 60 // Я больше привык, когда подобные значения вынесены в константы в отдельном файле, например DAY_IN_SECONDS
    ): void
    {
        try {
            $this->redis->setex($key, $expiresInSeconds, serialize($value));
        } catch (RedisException $e) {
            throw new ConnectorException('Connector error', $e->getCode(), $e);
        }
    }

    public function has($key): bool
    {
        return $this->redis->exists($key);
    }
}
