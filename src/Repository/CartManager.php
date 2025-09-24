<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Repository;

use Exception;
use Psr\Log\LoggerInterface;
use Raketa\BackendTestTask\Domain\Cart;
use Raketa\BackendTestTask\Infrastructure\Connector;
use Raketa\BackendTestTask\Infrastructure\ConnectorException;
use Raketa\BackendTestTask\Infrastructure\ConnectorFacade;

class CartManager
{
    public LoggerInterface $logger;

    public function __construct(private readonly Connector $connector) {}

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function saveCart(Cart $cart): void
    {
        try {
            $this->connector->set(session_id(), $cart);
        } catch (Exception $e) {
            $this->logger->error('Error');
        }
    }

    /**
     * @return ?Cart
     */
    public function getCart(): ?Cart
    {
        try {
            $redisValue = $this->connector->get(session_id());

            if (!empty($redisValue)) {
                return $redisValue;
            }
        } catch (ConnectorException|Exception $e) {
            $this->logger->error('Error: ' . $e->getMessage());
        }

        return Cart::createEmptyCart();
    }
}
