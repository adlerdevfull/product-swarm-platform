<?php

declare(strict_types=1);

namespace Infrastructure\Messaging;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:worker:orders', description: 'Consume order events from RabbitMQ')]
final class OrderWorkerCommand extends Command
{
    public function __construct(private readonly string $amqpUrl)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parts = parse_url($this->amqpUrl);
        $connection = new AMQPStreamConnection(
            $parts['host'] ?? 'rabbitmq',
            $parts['port'] ?? 5672,
            $parts['user'] ?? 'guest',
            $parts['pass'] ?? 'guest',
            ltrim($parts['path'] ?? '/', '/') ?: '/',
        );

        $channel = $connection->channel();
        $channel->exchange_declare('product_platform', 'topic', false, true, false);
        $channel->queue_declare('order_notifications', false, true, false, false);
        $channel->queue_bind('order_notifications', 'product_platform', 'order.*');
        $channel->basic_qos(0, 1, false);

        $output->writeln('<info>Listening on queue order_notifications...</info>');

        $channel->basic_consume(
            'order_notifications',
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $msg) use ($output): void {
                $body = $msg->getBody();
                $output->writeln(sprintf('[%s] %s', date('H:i:s'), $body));
                // In production: send email, push notification, update analytics, etc.
                $msg->ack();
            },
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return Command::SUCCESS;
    }
}
