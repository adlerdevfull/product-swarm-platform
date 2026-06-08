<?php

declare(strict_types=1);

namespace Domain\Product\Repositories;

use Domain\Product\Entities\Product;

interface ProductRepositoryInterface
{
    public function save(Product $product): Product;

    public function findById(int $id): ?Product;

    public function findBySku(string $sku): ?Product;

    /** @return Product[] */
    public function findAll(): array;

    /** @return Product[] */
    public function findPublished(): array;
}
