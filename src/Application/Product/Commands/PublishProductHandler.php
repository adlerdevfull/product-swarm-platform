<?php

declare(strict_types=1);

namespace Application\Product\Commands;

use Domain\Product\Entities\Product;
use Domain\Product\Repositories\ProductRepositoryInterface;

final readonly class PublishProductHandler
{
    public function __construct(private ProductRepositoryInterface $products) {}

    public function handle(int $productId): Product
    {
        $product = $this->products->findById($productId)
            ?? throw new \DomainException("Product not found: {$productId}");

        return $this->products->save($product->publish());
    }
}
