<?php

namespace Tests\Component;

use App\Contracts\ReconciliationServiceInterface;
use App\Dto\ReconciliationReport;
use App\Http\Controllers\ReconciliationController;
use Tests\TestCase;

final class ReconciliationControllerTest extends TestCase
{
    public function test_controller_returns_report_payload(): void
    {
        $report = new ReconciliationReport(
            [['id' => 'ord_paid', 'status' => 'delivering', 'amount' => 500, 'delivery_code' => null]],
            [],
            500,
            500,
        );
        $reconciliation = $this->createMock(ReconciliationServiceInterface::class);
        $reconciliation->expects($this->once())->method('report')->willReturn($report);

        $response = (new ReconciliationController())($reconciliation);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['ledger']['balanced']);
        $this->assertSame('ord_paid', $response->getData(true)['paid_not_delivered'][0]['id']);
    }
}
