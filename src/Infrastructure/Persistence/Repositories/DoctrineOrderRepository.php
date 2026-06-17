<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Domain\Order\Entities\Order;
use Domain\Order\Enums\OrderStatus;
use Domain\Order\Repositories\OrderRepositoryInterface;
use Domain\Shared\ValueObjects\Money;
use Infrastructure\Persistence\Models\OrderModel;

final class DoctrineOrderRepository implements OrderRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function save(Order $order): Order
    {
        if ($order->id === null) {
            $model = new OrderModel();
        } else {
            $model = $this->em->find(OrderModel::class, $order->id)
                ?? throw new \RuntimeException('Order model not found');
        }

        $model->orderNumber = $order->orderNumber;
        $model->userId = $order->userId;
        $model->productId = $order->productId;
        $model->productSku = $order->productSku;
        $model->quantity = $order->quantity;
        $model->unitPriceCents = $order->unitPrice->cents;
        $model->totalCents = $order->total->cents;
        $model->status = $order->status->value;
        $model->createdAt = $order->createdAt;

        $this->em->persist($model);
        $this->em->flush();

        return $this->toDomain($model);
    }

    public function findById(int $id): ?Order
    {
        $model = $this->em->find(OrderModel::class, $id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByUser(int $userId): array
    {
        return array_map(
            $this->toDomain(...),
            $this->em->getRepository(OrderModel::class)->findBy(['userId' => $userId], ['id' => 'DESC']),
        );
    }

    public function findAll(): array
    {
        return array_map(
            $this->toDomain(...),
            $this->em->getRepository(OrderModel::class)->findBy([], ['id' => 'DESC']),
        );
    }

    private function toDomain(OrderModel $model): Order
    {
        return Order::reconstitute(
            id: (int) $model->id,
            orderNumber: $model->orderNumber,
            userId: $model->userId,
            productId: $model->productId,
            productSku: $model->productSku,
            quantity: $model->quantity,
            unitPrice: new Money($model->unitPriceCents),
            total: new Money($model->totalCents),
            status: OrderStatus::from($model->status),
            createdAt: $model->createdAt,
        );
    }
}
