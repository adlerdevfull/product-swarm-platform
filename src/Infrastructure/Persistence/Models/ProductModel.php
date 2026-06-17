<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class ProductModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    public string $sku = '';

    #[ORM\Column(length: 200)]
    public string $name = '';

    #[ORM\Column(type: 'text')]
    public string $description = '';

    #[ORM\Column]
    public int $priceCents = 0;

    #[ORM\Column]
    public int $stock = 0;

    #[ORM\Column(length: 32)]
    public string $status = 'draft';

    #[ORM\Column]
    public \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
