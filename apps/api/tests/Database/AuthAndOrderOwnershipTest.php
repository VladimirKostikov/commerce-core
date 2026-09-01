<?php

namespace Tests\Database;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

final class AuthAndOrderOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_requires_user(): void
    {
        $this->expectException(QueryException::class);

        Order::factory()->create(['user_id' => null]);
    }

    public function test_personal_access_token_is_stored(): void
    {
        $user = User::factory()->create();
        $user->createToken('api');

        $this->assertSame(1, PersonalAccessToken::query()->where('tokenable_id', $user->id)->count());
    }
}
