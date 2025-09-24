<?php

declare(strict_types = 1);

namespace Raketa\BackendTestTask\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Raketa\BackendTestTask\Repository\Entity\Product;

readonly class ProductRepository
{
    public function __construct(private Connection $connection) {}

    /**
     * @throws Exception
     */
    public function getByUuid(string $uuid): ?Product
    {
        $row = $this->connection->fetchOne(
            'SELECT ' . $this->columnsForMake() . ' FROM products WHERE uuid = ' . $uuid,
        );

        if (empty($row)) {
            return null;
        }

        return $this->make($row);
    }

    /**
     * @throws Exception
     */
    public function getByCategory(string $category): array
    {
        return array_map(
            static fn (array $row): Product => $this->make($row),
            $this->connection->fetchAllAssociative(
                'SELECT ' . $this->columnsForMake() . ' FROM products WHERE is_active = 1 AND category = ' . $category,
            )
        );
    }

    public function make(array $row): Product
    {
        return new Product(
            $row['id'],
            $row['uuid'],
            $row['is_active'],
            $row['category'],
            $row['name'],
            $row['description'],
            $row['thumbnail'],
            $row['price'],
        );
    }

    private function columnsForMake(): string // TODO: Больше привык делать запросы через ORM сущности, конкретно Eloquent. Там бы сделал Scope
    {
        return 'id, uuid, is_active, category, name, description, thumbnail, price';
    }
}
