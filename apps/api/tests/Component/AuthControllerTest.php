<?php

namespace Tests\Component;

use App\Contracts\AuthServiceInterface;
use App\Dto\AuthToken;
use App\Http\Controllers\AuthController;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token_payload(): void
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'Ada';
        $user->email = 'ada@example.com';

        $auth = $this->createMock(AuthServiceInterface::class);
        $auth->method('register')->willReturn(new AuthToken('plain-token', $user));

        $request = RegisterRequest::create('/api/register', 'POST', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ]);
        $request->setContainer($this->app);
        $request->validateResolved();

        $response = (new AuthController())->register($request, $auth);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('plain-token', $response->getData(true)['token']);
        $this->assertSame('Bearer', $response->getData(true)['token_type']);
    }

    public function test_logout_calls_service(): void
    {
        $user = User::factory()->make();
        $auth = $this->createMock(AuthServiceInterface::class);
        $auth->expects($this->once())->method('logout')->with($user);

        $request = Request::create('/api/logout', 'POST');
        $request->setUserResolver(fn () => $user);

        $response = (new AuthController())->logout($request, $auth);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['ok']);
    }
}
