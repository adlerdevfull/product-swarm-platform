<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Application\Product\Commands\CreateProductHandler;
use Application\Product\Commands\PublishProductHandler;
use Domain\Product\Entities\Product;
use Domain\Product\Repositories\ProductRepositoryInterface;
use Infrastructure\Metrics\PrometheusMetrics;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ProductController extends AbstractController
{
    public function __construct(private readonly PrometheusMetrics $metrics) {}

    #[Route('/api/v1/products', methods: ['GET'])]
    public function index(ProductRepositoryInterface $products): JsonResponse
    {
        $start = microtime(true);
        $list = array_map($this->serialize(...), $products->findAll());
        $this->metrics->increment('http_requests_total', ['route' => 'products_index', 'method' => 'GET']);
        $this->metrics->observe('http_request_duration_seconds', microtime(true) - $start, ['route' => 'products_index']);

        return new JsonResponse(['data' => $list]);
    }

    #[Route('/api/v1/products', methods: ['POST'])]
    public function create(Request $request, CreateProductHandler $handler): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        try {
            $product = $handler->handle(
                sku: (string) ($body['sku'] ?? ''),
                name: (string) ($body['name'] ?? ''),
                description: (string) ($body['description'] ?? ''),
                priceCents: (int) ($body['price_cents'] ?? 0),
                stock: (int) ($body['stock'] ?? 0),
            );
            $this->metrics->increment('http_requests_total', ['route' => 'products_create', 'method' => 'POST']);
            $this->metrics->increment('products_created_total');

            return new JsonResponse(['data' => $this->serialize($product)], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/api/v1/products/{id}/publish', methods: ['POST'])]
    public function publish(int $id, PublishProductHandler $handler): JsonResponse
    {
        try {
            $product = $handler->handle($id);
            $this->metrics->increment('products_published_total');

            return new JsonResponse(['data' => $this->serialize($product)]);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/api/v1/products/{id}', methods: ['GET'])]
    public function show(int $id, ProductRepositoryInterface $products): JsonResponse
    {
        $product = $products->findById($id);
        if ($product === null) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $this->serialize($product)]);
    }

    /** @return array<string, mixed> */
    private function serialize(Product $p): array
    {
        return [
            'id' => $p->id,
            'sku' => $p->sku,
            'name' => $p->name,
            'description' => $p->description,
            'price_cents' => $p->price->cents,
            'price_euros' => $p->price->toEuros(),
            'stock' => $p->stock,
            'status' => $p->status->value,
            'created_at' => $p->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
