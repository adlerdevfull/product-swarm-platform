<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Application\Order\Commands\PlaceOrderHandler;
use Application\Order\Commands\TransitionOrderHandler;
use Domain\Order\Entities\Order;
use Domain\Order\Enums\OrderStatus;
use Domain\Order\Repositories\OrderRepositoryInterface;
use Infrastructure\Metrics\PrometheusMetrics;
use Infrastructure\Persistence\Models\UserModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class OrderController extends AbstractController
{
    public function __construct(private readonly PrometheusMetrics $metrics) {}

    #[Route('/api/v1/orders', methods: ['GET'])]
    public function index(OrderRepositoryInterface $orders): JsonResponse
    {
        return new JsonResponse([
            'data' => array_map($this->serialize(...), $orders->findAll()),
        ]);
    }

    #[Route('/api/v1/orders', methods: ['POST'])]
    public function place(Request $request, PlaceOrderHandler $handler): JsonResponse
    {
        /** @var UserModel $user */
        $user = $this->getUser();
        $body = json_decode($request->getContent(), true) ?? [];

        try {
            $order = $handler->handle(
                userId: (int) $user->getId(),
                productId: (int) ($body['product_id'] ?? 0),
                quantity: (int) ($body['quantity'] ?? 1),
            );
            $this->metrics->increment('orders_placed_total');
            $this->metrics->increment('http_requests_total', ['route' => 'orders_place', 'method' => 'POST']);

            return new JsonResponse(['data' => $this->serialize($order)], Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    #[Route('/api/v1/orders/{id}/transition', methods: ['POST'])]
    public function transition(int $id, Request $request, TransitionOrderHandler $handler): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $status = (string) ($body['status'] ?? '');

        try {
            $next = OrderStatus::from($status);
            $order = $handler->handle($id, $next);
            $this->metrics->increment('orders_transitioned_total', ['status' => $next->value]);

            return new JsonResponse(['data' => $this->serialize($order)]);
        } catch (\ValueError) {
            return new JsonResponse(['error' => "Invalid status: {$status}"], Response::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /** @return array<string, mixed> */
    private function serialize(Order $o): array
    {
        return [
            'id' => $o->id,
            'order_number' => $o->orderNumber,
            'user_id' => $o->userId,
            'product_id' => $o->productId,
            'product_sku' => $o->productSku,
            'quantity' => $o->quantity,
            'unit_price_cents' => $o->unitPrice->cents,
            'total_cents' => $o->total->cents,
            'total_euros' => $o->total->toEuros(),
            'status' => $o->status->value,
            'created_at' => $o->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
