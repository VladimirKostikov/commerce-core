<?php

namespace App\Services\Messaging;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

final class RabbitMqBroker
{
    public function publish(string $body): void
    {
        $session = $this->open();

        try {
            $this->declareQueue($session);
            $session->basic_publish($this->message($body), '', $this->queue());
        } finally {
            $this->close($session);
        }
    }

    public function get(): ?string
    {
        $session = $this->open();

        try {
            $this->declareQueue($session);
            $message = $session->basic_get($this->queue(), true);

            return $message?->getBody();
        } finally {
            $this->close($session);
        }
    }

    private function open(): AMQPChannel
    {
        $connection = new AMQPStreamConnection(
            (string) config('messaging.rabbitmq.host'),
            (int) config('messaging.rabbitmq.port'),
            (string) config('messaging.rabbitmq.user'),
            (string) config('messaging.rabbitmq.password'),
            (string) config('messaging.rabbitmq.vhost', '/'),
            false,
            'AMQPLAIN',
            null,
            'en_US',
            $this->timeout(),
            $this->timeout(),
        );

        return $connection->channel();
    }

    private function close(AMQPChannel $channel): void
    {
        try {
            $connection = $channel->getConnection();
            $channel->close();
            $connection?->close();
        } catch (Throwable) {
        }
    }

    private function declareQueue(AMQPChannel $channel): void
    {
        $channel->queue_declare($this->queue(), false, true, false, false);
    }

    private function message(string $body): AMQPMessage
    {
        return new AMQPMessage($body, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/json',
        ]);
    }

    private function queue(): string
    {
        return (string) config('messaging.rabbitmq.queue');
    }

    private function timeout(): float
    {
        return (float) config('messaging.rabbitmq.timeout', 3);
    }
}
