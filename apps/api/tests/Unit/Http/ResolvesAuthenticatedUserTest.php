<?php

namespace Tests\Unit\Http;

use App\Http\Controllers\Concerns\ResolvesAuthenticatedUser;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Tests\TestCase;

final class ResolvesAuthenticatedUserTest extends TestCase
{
    public function test_returns_user_from_request(): void
    {
        $user = new User();
        $request = Request::create('/api/me', 'GET');
        $request->setUserResolver(fn () => $user);

        $this->assertSame($user, $this->resolver()->authenticatedUser($request));
    }

    public function test_rejects_missing_user(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->resolver()->authenticatedUser(Request::create('/api/me', 'GET'));
    }

    private function resolver(): object
    {
        return new class
        {
            use ResolvesAuthenticatedUser {
                authenticatedUser as public;
            }
        };
    }
}
