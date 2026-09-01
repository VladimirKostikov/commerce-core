<?php

namespace Tests\Unit\Models;

use App\Models\ProductKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductKeyMutatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_code_is_normalized(): void
    {
        $key = ProductKey::factory()->create([
            'code' => '  lfxc-tncs-bpcd  ',
        ]);

        $this->assertSame('LFXC-TNCS-BPCD', $key->code);
    }
}
