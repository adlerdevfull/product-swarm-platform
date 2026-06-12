<?php

declare(strict_types=1);

namespace Application\Product\Commands;

use Domain\Product\Entities\Product;
use Domain\Product\Repositories\ProductRepositoryInterface;
use Domain\Shared\ValueObjects\Money;

final readonly class CreateProductHandler
{
    public function __construct(private ProductRepositoryInterface $products) {}

    public function handle(
        string $sku,
        string $name,
        string $description,
        int $priceCents,
        int $stock,
    ): Product {
        if ($this->products->findBySku(strtoupper(trim($sku))) !== null) {
            throw new \DomainException("SKU already exists: {$sku}");
        }

        $product = Product::create(
            sku: $sku,
            name: $name,
            description: $description,
            price: new Money($priceCents),
            stock: $stock,
        );

        return $this->products->save($product);
    }
}
