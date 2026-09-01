<?php

namespace Tests\System;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use RdKafka\Conf;
use RdKafka\Producer;
use Tests\Support\InfrastructureHost;

final class BrokerProtocolTest extends TestCase
{
    public function test_kafka_metadata_lists_a_broker(): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->kafkaBrokers());
        $conf->set('socket.timeout.ms', '3000');

        $producer = new Producer($conf);
        $metadata = $producer->getMetadata(true, null, 3000);

        $this->assertGreaterThan(0, count($metadata->getBrokers()));
    }

    public function test_rabbitmq_declares_a_durable_queue(): void
    {
        $connection = new AMQPStreamConnection(
            InfrastructureHost::resolve('rabbitmq'),
            5672,
            'guest',
            'guest',
            '/',
            false,
            'AMQPLAIN',
            null,
            'en_US',
            3.0,
            3.0,
        );
        $channel = $connection->channel();
        [$queue] = $channel->queue_declare('commerce.health', false, true, false, false);

        $this->assertSame('commerce.health', $queue);

        $channel->close();
        $connection->close();
    }

    private function kafkaBrokers(): string
    {
        return InfrastructureHost::resolve('kafka').':'.InfrastructureHost::kafkaPort();
    }
}
