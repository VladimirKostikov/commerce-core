<?php

namespace Tests\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthApiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_json_contract(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])
            ->assertCreated()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email'],
            ]);
    }

    public function test_login_validation_contract(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
