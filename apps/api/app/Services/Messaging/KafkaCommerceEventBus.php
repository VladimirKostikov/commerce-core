<?php

namespace App\Services\Messaging;

use App\Contracts\CommerceEventBusInterface;
use App\Dto\CommerceLog;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use RdKafka\Producer;
use Throwable;

final class KafkaCommerceEventBus implements CommerceEventBusInterface
{
    public function __construct(
        private readonly CommerceEventCodec $codec,
    ) {
    }

    public function publish(CommerceLog $log): void
    {
        try {
            $this->produce($this->codec->encode($log), $log->orderId);
        } catch (Throwable) {
        }
    }

    public function pull(int $timeoutMs): ?CommerceLog
    {
        try {
            $payload = $this->consume($timeoutMs);
        } catch (Throwable) {
            return null;
        }

        if ($payload === null) {
            return null;
        }

        try {
            return $this->codec->decode($payload);
        } catch (Throwable) {
            return null;
        }
    }

    private function produce(string $payload, string $key): void
    {
        if (! class_exists(Producer::class)) {
            return;
        }

        $producer = new Producer($this->producerConf());
        $producer->newTopic($this->topic())->produce(RD_KAFKA_PARTITION_UA, 0, $payload, $key);
        $producer->poll(0);
        $producer->flush($this->timeoutMs());
    }

    private function consume(int $timeoutMs): ?string
    {
        if (! class_exists(KafkaConsumer::class)) {
            return null;
        }

        $consumer = new KafkaConsumer($this->consumerConf());
        $consumer->subscribe([$this->topic()]);

        try {
            return $this->read($consumer, $timeoutMs);
        } finally {
            $consumer->close();
        }
    }

    private function read(KafkaConsumer $consumer, int $timeoutMs): ?string
    {
        $deadline = $this->nowMs() + $timeoutMs;

        while ($this->nowMs() < $deadline) {
            $message = $consumer->consume($this->slice($deadline));

            if ($message !== null && $message->err === RD_KAFKA_RESP_ERR_NO_ERROR) {
                $consumer->commit($message);

                return $message->payload;
            }
        }

        return null;
    }

    private function slice(int $deadline): int
    {
        return max(100, min(1000, $deadline - $this->nowMs()));
    }

    private function nowMs(): int
    {
        return (int) (microtime(true) * 1000);
    }

    private function producerConf(): Conf
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->brokers());
        $conf->set('socket.timeout.ms', (string) $this->timeoutMs());
        $conf->set('message.timeout.ms', (string) $this->timeoutMs());

        return $conf;
    }

    private function consumerConf(): Conf
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->brokers());
        $conf->set('group.id', $this->group());
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('enable.partition.eof', 'true');
        $conf->set('enable.auto.commit', 'false');
        $conf->set('socket.timeout.ms', (string) $this->timeoutMs());

        return $conf;
    }

    private function brokers(): string
    {
        return (string) config('messaging.kafka.brokers');
    }

    private function topic(): string
    {
        return (string) config('messaging.kafka.topic');
    }

    private function group(): string
    {
        return (string) config('messaging.kafka.group');
    }

    private function timeoutMs(): int
    {
        return (int) config('messaging.kafka.timeout_ms', 3000);
    }
}
