<?php

namespace Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class PaymentWebhookApiContractTest extends TestCase
{
    use RefreshDatabase;
    public function test_webhook_json_contract(): void
    {
        $this->postJson('/api/webhook/payment', PaymentWebhookPayload::paid('ord_00123', 'evt_a1b2c3'))
            ->assertOk()
            ->assertJsonStructure(['accepted']);
    }

    public function test_duplicate_event_id_stays_accepted(): void
    {
        $payload = PaymentWebhookPayload::paid('ord_00123', 'evt_dup_contract');

        $this->postJson('/api/webhook/payment', $payload)
            ->assertOk()
            ->assertJsonPath('accepted', true);
        $this->postJson('/api/webhook/payment', $payload)
            ->assertOk()
            ->assertJsonPath('accepted', true);
    }

    public function test_webhook_validation_contract(): void
    {
        $this->postJson('/api/webhook/payment', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'event_id',
                'order_id',
                'status',
                'amount',
                'currency',
                'created_at',
            ]);
    }
}
