<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class OrderModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 32, unique: true)]
    public string $orderNumber = '';

    #[ORM\Column]
    public int $userId = 0;

    #[ORM\Column]
    public int $productId = 0;

    #[ORM\Column(length: 64)]
    public string $productSku = '';

    #[ORM\Column]
    public int $quantity = 0;

    #[ORM\Column]
    public int $unitPriceCents = 0;

    #[ORM\Column]
    public int $totalCents = 0;

    #[ORM\Column(length: 32)]
    public string $status = 'pending';

    #[ORM\Column]
    public \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
