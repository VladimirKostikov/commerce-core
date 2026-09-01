<?php

namespace Tests\Component;

use App\Contracts\PaymentWebhookServiceInterface;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Requests\PaymentWebhookRequest;
use Tests\Support\PaymentWebhookPayload;
use Tests\TestCase;

final class PaymentWebhookControllerTest extends TestCase
{
    public function test_controller_accepts_payload(): void
    {
        $webhooks = $this->createMock(PaymentWebhookServiceInterface::class);
        $webhooks->expects($this->once())->method('handle');

        $request = PaymentWebhookRequest::create(
            '/api/webhook/payment',
            'POST',
            PaymentWebhookPayload::paid('ord_00123', 'evt_a1b2c3'),
        );
        $request->setContainer($this->app);
        $request->validateResolved();

        $response = (new PaymentWebhookController())($request, $webhooks);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['accepted']);
    }
}
