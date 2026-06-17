<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Infrastructure\Metrics\PrometheusMetrics;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MetricsController extends AbstractController
{
    public function __construct(private readonly PrometheusMetrics $metrics) {}

    #[Route('/metrics', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response(
            $this->metrics->render(),
            200,
            ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8'],
        );
    }
}
