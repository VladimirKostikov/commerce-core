<?php

namespace App\Services\Payments;

use App\Contracts\CommerceLoggerInterface;
use App\Contracts\CommerceWorkQueueInterface;
use App\Contracts\DeliveryServiceInterface;
use App\Contracts\LedgerWriterInterface;
use App\Contracts\PaymentWebhookServiceInterface;
use App\Dto\CommerceWork;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\Logging\CommerceLogFactory;
use App\Services\Messaging\SideEffectScheduler;
use App\Services\Orders\OrderStatusMachine;
use Illuminate\Support\Facades\DB;

final class PaymentWebhookService implements PaymentWebhookServiceInterface
{
    public function __construct(
        private readonly PaymentEventStore $events,
        private readonly DeliveryServiceInterface $deliveryService,
        private readonly OrderStatusMachine $statusMachine,
        private readonly LedgerWriterInterface $ledger,
        private readonly CommerceLoggerInterface $logger,
        private readonly CommerceLogFactory $logs,
        private readonly CommerceWorkQueueInterface $work,
        private readonly SideEffectScheduler $sideEffects,
    ) {
    }

    public function handle(array $payload): void
    {
        $this->apply($this->events->remember($payload));
    }

    public function applyPendingForOrder(Order $order): void
    {
        $events = PaymentEvent::query()
            ->where('order_id', $order->id)
            ->whereNull('processed_at')
            ->orderBy('id')
            ->get();

        foreach ($events as $event) {
            $this->apply($event);
        }
    }

    public function apply(PaymentEvent $event): void
    {
        if ($event->processed_at !== null) {
            return;
        }

        if (Order::query()->find($event->order_id) === null) {
            return;
        }

        DB::transaction(fn () => $this->settle($event));
    }

    private function settle(PaymentEvent $event): void
    {
        $lockedEvent = PaymentEvent::query()->lockForUpdate()->find($event->id);

        if ($lockedEvent === null || $lockedEvent->processed_at !== null) {
            return;
        }

        $order = Order::query()->lockForUpdate()->find($lockedEvent->order_id);

        if ($order === null) {
            return;
        }

        if ($lockedEvent->status === PaymentStatus::Failed) {
            $this->failPayment($order, $lockedEvent);

            return;
        }

        $this->acceptPayment($order, $lockedEvent);
    }

    private function failPayment(Order $order, PaymentEvent $event): void
    {
        $this->statusMachine->transition($order, OrderStatus::PaymentFailed);
        $order->save();
        $this->markProcessed($event);
        $this->sideEffects->run(function () use ($order, $event): void {
            $this->logger->record($this->logs->paymentFailed($order, $event));
            $this->work->push(new CommerceWork($order->id, 'payment_failed'));
        });
    }

    private function acceptPayment(Order $order, PaymentEvent $event): void
    {
        if ($this->statusMachine->transition($order, OrderStatus::Paid)) {
            $order->save();
            $this->ledger->postPayment($order);
            $this->sideEffects->run(function () use ($order, $event): void {
                $this->logger->record($this->logs->paymentAccepted($order, $event));
                $this->work->push(new CommerceWork($order->id, 'payment_accepted'));
            });
        }

        if (! $order->status->blocksDelivery()) {
            $this->deliveryService->deliver($order);
        }

        $this->markProcessed($event);
    }

    private function markProcessed(PaymentEvent $event): void
    {
        $event->processed_at = now();
        $event->save();
    }
}
