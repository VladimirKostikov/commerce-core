<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class ReconciliationLiveTest extends TestCase
{
    public function test_reconciliation_endpoint_via_nginx(): void
    {
        $response = HttpProbe::get(InfrastructureHost::appUrl().'/api/reconciliation');
        $payload = json_decode($response['body'], true);

        $this->assertSame(200, $response['status']);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('paid_not_delivered', $payload);
        $this->assertArrayHasKey('delivered_not_paid', $payload);
        $this->assertTrue($payload['ledger']['balanced'] ?? false);
    }
}
