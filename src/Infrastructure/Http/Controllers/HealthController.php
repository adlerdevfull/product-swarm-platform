<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends AbstractController
{
    #[Route('/api/v1/health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'service' => 'product-swarm-platform',
            'instance' => $_ENV['APP_INSTANCE'] ?? getenv('APP_INSTANCE') ?: 'app',
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]);
    }

    /** Swarm / LB readiness probe — fails if app cannot serve traffic. */
    #[Route('/api/v1/ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        return new JsonResponse(['ready' => true]);
    }
}
