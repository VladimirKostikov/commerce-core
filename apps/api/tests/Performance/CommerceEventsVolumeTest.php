<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class CommerceEventsVolumeTest extends TestCase
{
    public function test_volume_of_commerce_events_can_be_stored(): void
    {
        $base = InfrastructureHost::clickHouseUrl();
        $batch = uniqid('commerce_', true);

        for ($i = 0; $i < 25; $i++) {
            $row = json_encode([
                'channel' => 'payment',
                'event' => 'accepted',
                'order_id' => 'ord_'.$batch,
                'reference' => $batch.'_'.$i,
                'status' => 'paid',
                'message' => $batch,
                'context' => '{}',
            ], JSON_THROW_ON_ERROR);
            $sql = 'INSERT INTO logs.commerce_events FORMAT JSONEachRow '.$row;
            $response = HttpProbe::post($base.'/', $sql);
            $this->assertSame(200, $response['status']);
        }

        $countSql = sprintf(
            "SELECT count() FROM logs.commerce_events WHERE message = '%s'",
            $batch,
        );
        $response = HttpProbe::get($base.'/?query='.rawurlencode($countSql));

        $this->assertSame(200, $response['status']);
        $this->assertSame('25', trim($response['body']));
    }
}
