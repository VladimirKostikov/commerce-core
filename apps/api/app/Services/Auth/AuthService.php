<?php

namespace App\Services\Auth;

use App\Contracts\AuthServiceInterface;
use App\Dto\AuthToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

final class AuthService implements AuthServiceInterface
{
    public function register(string $name, string $email, string $password): AuthToken
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        return $this->tokenFor($user);
    }

    public function login(string $email, string $password): AuthToken
    {
        $user = User::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return $this->tokenFor($user);
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    private function tokenFor(User $user): AuthToken
    {
        return new AuthToken($user->createToken('api')->plainTextToken, $user);
    }
}
