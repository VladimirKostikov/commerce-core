<?php

namespace Tests\Unit\Services\Messaging;

use App\Dto\CommerceLog;
use App\Services\Messaging\CommerceEventCodec;
use JsonException;
use Tests\TestCase;

final class CommerceEventCodecTest extends TestCase
{
    public function test_round_trip_keeps_fields(): void
    {
        $log = new CommerceLog(
            'payment',
            'accepted',
            'ord_1',
            'evt_1',
            'paid',
            'payment accepted',
            ['amount' => 500],
        );

        $codec = new CommerceEventCodec();
        $decoded = $codec->decode($codec->encode($log));

        $this->assertSame('payment', $decoded->channel);
        $this->assertSame('accepted', $decoded->event);
        $this->assertSame('ord_1', $decoded->orderId);
        $this->assertSame('evt_1', $decoded->reference);
        $this->assertSame(500, $decoded->context['amount']);
    }

    public function test_decode_rejects_invalid_json(): void
    {
        $this->expectException(JsonException::class);
        (new CommerceEventCodec())->decode('not-json');
    }
}
