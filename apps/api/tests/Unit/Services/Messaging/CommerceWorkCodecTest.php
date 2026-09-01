<?php

namespace Tests\Unit\Services\Messaging;

use App\Dto\CommerceWork;
use App\Services\Messaging\CommerceWorkCodec;
use JsonException;
use Tests\TestCase;

final class CommerceWorkCodecTest extends TestCase
{
    public function test_round_trip_keeps_fields(): void
    {
        $codec = new CommerceWorkCodec();
        $decoded = $codec->decode($codec->encode(new CommerceWork('ord_9', 'issued')));

        $this->assertSame('ord_9', $decoded->orderId);
        $this->assertSame('issued', $decoded->kind);
    }

    public function test_decode_rejects_incomplete_payload(): void
    {
        $this->expectException(JsonException::class);
        (new CommerceWorkCodec())->decode('{"order_id":"x"}');
    }
}
