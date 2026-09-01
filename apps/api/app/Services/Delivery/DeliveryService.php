<?php

namespace App\Services\Delivery;

use App\Contracts\CommerceLoggerInterface;
use App\Contracts\CommerceWorkQueueInterface;
use App\Contracts\DeliveryServiceInterface;
use App\Contracts\LedgerWriterInterface;
use App\Contracts\SupplierInterface;
use App\Dto\CommerceWork;
use App\Dto\DeliveryAttempt;
use App\Dto\SupplierIssueResult;
use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\Logging\CommerceLogFactory;
use App\Services\Messaging\SideEffectScheduler;
use Illuminate\Support\Facades\DB;

final class DeliveryService implements DeliveryServiceInterface
{
    public function __construct(
        private readonly SupplierInterface $supplier,
        private readonly DeliveryGate $gate,
        private readonly DeliveryStore $store,
        private readonly DeliveryOutcomeWriter $outcomes,
        private readonly LedgerWriterInterface $ledger,
        private readonly CommerceLoggerInterface $logger,
        private readonly CommerceLogFactory $logs,
        private readonly CommerceWorkQueueInterface $work,
        private readonly SideEffectScheduler $sideEffects,
    ) {
    }

    public function deliver(Order $order): Order
    {
        return $this->run($order, false);
    }

    public function recover(Order $order): Order
    {
        return $this->run($order, true);
    }

    private function run(Order $order, bool $allowStale): Order
    {
        $attempt = DB::transaction(fn (): ?DeliveryAttempt => $this->prepare($order, $allowStale));

        if ($attempt === null) {
            return $order;
        }

        if (! $attempt->shouldIssue) {
            return $attempt->order;
        }

        $result = $this->supplier->issue(
            $attempt->requestId,
            $attempt->sku,
            $attempt->order->id,
        );

        return DB::transaction(fn (): Order => $this->complete($attempt, $result));
    }

    private function prepare(Order $order, bool $allowStale): ?DeliveryAttempt
    {
        $locked = Order::query()->lockForUpdate()->find($order->id);

        if ($locked === null) {
            return null;
        }

        if ($this->gate->isTerminal($locked)) {
            return new DeliveryAttempt($locked, (string) $locked->delivery_request_id, $locked->sku, false);
        }

        if ($this->gate->isInFlight($locked) && ! $this->shouldRetryInFlight($locked, $allowStale)) {
            return new DeliveryAttempt($locked, (string) $locked->delivery_request_id, $locked->sku, false);
        }

        if (! $this->gate->isInFlight($locked)) {
            $this->gate->start($locked);
            $locked->save();
        }

        $delivery = $this->store->findOrCreate($locked);

        return new DeliveryAttempt($locked, $delivery->request_id, $locked->sku, true);
    }

    private function shouldRetryInFlight(Order $order, bool $allowStale): bool
    {
        return $allowStale && $this->gate->isStale($order);
    }

    private function complete(DeliveryAttempt $attempt, SupplierIssueResult $result): Order
    {
        $order = Order::query()->lockForUpdate()->find($attempt->order->id);

        if ($order === null || $this->gate->isTerminal($order)) {
            return $order ?? $attempt->order;
        }

        $delivery = Delivery::query()
            ->where('order_id', $order->id)
            ->lockForUpdate()
            ->first();

        if ($delivery === null) {
            return $order;
        }

        $this->syncRequestId($order, $delivery, $result);
        $order = $this->outcomes->apply($order, $delivery, $result);
        $this->finish($order, $result);

        return $order;
    }

    private function finish(Order $order, SupplierIssueResult $result): void
    {
        if ($order->status === OrderStatus::Delivered) {
            $this->ledger->postIssue($order);
        }

        $this->sideEffects->run(function () use ($order, $result): void {
            $this->logger->record($this->logs->delivery($order, $result));
            $this->notifyIssued($order);
        });
    }

    private function notifyIssued(Order $order): void
    {
        if ($order->status !== OrderStatus::Delivered) {
            return;
        }

        $this->work->push(new CommerceWork($order->id, 'issued'));
    }

    private function syncRequestId(Order $order, Delivery $delivery, SupplierIssueResult $result): void
    {
        if ($result->requestId === null || $result->requestId === $delivery->request_id) {
            return;
        }

        $delivery->request_id = $result->requestId;
        $order->delivery_request_id = $result->requestId;
    }
}
