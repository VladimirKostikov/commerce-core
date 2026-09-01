<?php

namespace Tests\Unit\Services\Auth;

use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

final class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_issues_token(): void
    {
        $result = (new AuthService())->register('Ada', 'ada@example.com', 'password1');

        $this->assertNotSame('', $result->token);
        $this->assertSame('ada@example.com', $result->user->email);
        $this->assertSame(1, PersonalAccessToken::query()->count());
    }

    public function test_login_rejects_bad_password(): void
    {
        User::factory()->create([
            'email' => 'ada@example.com',
            'password' => 'password1',
        ]);

        $this->expectException(AuthenticationException::class);

        (new AuthService())->login('ada@example.com', 'wrong-pass');
    }

    public function test_logout_deletes_current_token(): void
    {
        $service = new AuthService();
        $result = $service->login(
            User::factory()->create([
                'email' => 'ada@example.com',
                'password' => 'password1',
            ])->email,
            'password1',
        );

        $user = $result->user;
        $user->withAccessToken(PersonalAccessToken::query()->firstOrFail());
        $service->logout($user);

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }
}
