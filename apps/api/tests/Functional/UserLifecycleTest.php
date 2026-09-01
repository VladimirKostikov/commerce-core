<?php

namespace Tests\Functional;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_user_can_be_fetched_by_email(): void
    {
        User::factory()->create([
            'name' => 'Alan Turing',
            'email' => 'Alan@Example.com',
        ]);

        $found = User::query()->where('email', 'alan@example.com')->first();

        $this->assertNotNull($found);
        $this->assertSame('Alan Turing', $found->name);
    }
}
