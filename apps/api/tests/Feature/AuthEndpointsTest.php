<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

final class AuthEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_issue_bearer_token(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ada',
            'email' => 'Ada@Example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])
            ->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'ada@example.com');

        $login = $this->postJson('/api/login', [
            'email' => 'ada@example.com',
            'password' => 'password1',
        ])->assertOk();

        $token = $login->json('token');
        $this->assertIsString($token);

        $this->getJson('/api/me', ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('email', 'ada@example.com');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'password1',
        ]);

        $this->postJson('/api/login', [
            'email' => 'ada@example.com',
            'password' => 'nope-nope',
        ])->assertUnauthorized();
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'password1',
        ]);
        $token = $user->createToken('api')->plainTextToken;

        $this->postJson('/api/logout', [], ['Authorization' => 'Bearer '.$token])
            ->assertOk();

        $this->assertSame(0, PersonalAccessToken::query()->count());

        Auth::forgetGuards();
        $this->flushHeaders();

        $this->getJson('/api/me', ['Authorization' => 'Bearer '.$token])
            ->assertUnauthorized();
    }

    public function test_protected_routes_require_token(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->postJson('/api/orders', ['sku' => 'STEAM-TOPUP-500'])->assertUnauthorized();
    }
}
