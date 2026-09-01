<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserMutatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_is_trimmed(): void
    {
        $user = User::factory()->create([
            'name' => '  Ada Lovelace  ',
        ]);

        $this->assertSame('Ada Lovelace', $user->name);
    }

    public function test_email_is_normalized(): void
    {
        $user = User::factory()->create([
            'email' => '  Ada@Example.COM  ',
        ]);

        $this->assertSame('ada@example.com', $user->email);
    }
}
