<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Product\Entities\Product;
use Domain\Product\Enums\ProductStatus;
use Domain\Product\Repositories\ProductRepositoryInterface;
use Domain\Shared\ValueObjects\Money;
use Infrastructure\Persistence\Models\ProductModel;

final class DoctrineProductRepository implements ProductRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function save(Product $product): Product
    {
        if ($product->id === null) {
            $model = new ProductModel();
        } else {
            $model = $this->em->find(ProductModel::class, $product->id)
                ?? throw new \RuntimeException('Product model not found');
        }

        $model->sku = $product->sku;
        $model->name = $product->name;
        $model->description = $product->description;
        $model->priceCents = $product->price->cents;
        $model->stock = $product->stock;
        $model->status = $product->status->value;
        $model->createdAt = $product->createdAt;

        $this->em->persist($model);
        $this->em->flush();

        return $this->toDomain($model);
    }

    public function findById(int $id): ?Product
    {
        $model = $this->em->find(ProductModel::class, $id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findBySku(string $sku): ?Product
    {
        $model = $this->em->getRepository(ProductModel::class)->findOneBy(['sku' => strtoupper($sku)]);
        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        return array_map(
            $this->toDomain(...),
            $this->em->getRepository(ProductModel::class)->findBy([], ['id' => 'DESC']),
        );
    }

    public function findPublished(): array
    {
        return array_map(
            $this->toDomain(...),
            $this->em->getRepository(ProductModel::class)->findBy(
                ['status' => ProductStatus::Published->value],
                ['id' => 'DESC'],
            ),
        );
    }

    private function toDomain(ProductModel $model): Product
    {
        return Product::reconstitute(
            id: (int) $model->id,
            sku: $model->sku,
            name: $model->name,
            description: $model->description,
            price: new Money($model->priceCents),
            stock: $model->stock,
            status: ProductStatus::from($model->status),
            createdAt: $model->createdAt,
        );
    }
}
