<?php

declare(strict_types=1);

namespace Domain\Order\Repositories;

use Domain\Order\Entities\Order;

interface OrderRepositoryInterface
{
    public function save(Order $order): Order;

    public function findById(int $id): ?Order;

    /** @return Order[] */
    public function findByUser(int $userId): array;

    /** @return Order[] */
    public function findAll(): array;
}
