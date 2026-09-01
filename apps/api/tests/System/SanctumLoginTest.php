<?php

namespace Tests\System;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class SanctumLoginTest extends TestCase
{
    public function test_seeded_user_can_login_via_nginx(): void
    {
        $response = HttpProbe::json('POST', InfrastructureHost::appUrl().'/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
        $payload = json_decode($response['body'], true);

        $this->assertSame(200, $response['status'], $response['body']);
        $this->assertIsArray($payload);
        $this->assertSame('Bearer', $payload['token_type'] ?? null);
        $this->assertNotSame('', $payload['token'] ?? '');
        $this->assertSame('test@example.com', $payload['user']['email'] ?? null);
    }
}
