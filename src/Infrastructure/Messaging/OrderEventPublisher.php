<?php

declare(strict_types=1);

namespace Infrastructure\Messaging;

use Domain\Order\Entities\Order;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Publishes order domain events to RabbitMQ.
 * Failures are logged but never block the core business flow.
 */
final class OrderEventPublisher
{
    private const EXCHANGE = 'product_platform';

    public function __construct(
        private readonly string $amqpUrl,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    public function orderPlaced(Order $order): void
    {
        $this->publish('order.placed', [
            'order_id' => $order->id,
            'order_number' => $order->orderNumber,
            'user_id' => $order->userId,
            'product_sku' => $order->productSku,
            'quantity' => $order->quantity,
            'total_cents' => $order->total->cents,
            'status' => $order->status->value,
            'event' => 'order.placed',
            'occurred_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]);
    }

    public function orderStatusChanged(Order $order): void
    {
        $this->publish('order.status_changed', [
            'order_id' => $order->id,
            'order_number' => $order->orderNumber,
            'status' => $order->status->value,
            'event' => 'order.status_changed',
            'occurred_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function publish(string $routingKey, array $payload): void
    {
        try {
            $parts = parse_url($this->amqpUrl);
            $connection = new AMQPStreamConnection(
                $parts['host'] ?? 'rabbitmq',
                $parts['port'] ?? 5672,
                $parts['user'] ?? 'guest',
                $parts['pass'] ?? 'guest',
                ltrim($parts['path'] ?? '/', '/') ?: '/',
            );
            $channel = $connection->channel();
            $channel->exchange_declare(self::EXCHANGE, 'topic', false, true, false);
            $channel->queue_declare('order_notifications', false, true, false, false);
            $channel->queue_bind('order_notifications', self::EXCHANGE, 'order.*');

            $message = new AMQPMessage(
                json_encode($payload, JSON_THROW_ON_ERROR),
                ['content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT],
            );
            $channel->basic_publish($message, self::EXCHANGE, $routingKey);

            $channel->close();
            $connection->close();
        } catch (\Throwable $e) {
            $this->logger?->warning('RabbitMQ publish failed (non-blocking)', [
                'routing_key' => $routingKey,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
