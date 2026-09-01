<?php

namespace Tests\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UserPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_stored_and_read(): void
    {
        $user = User::factory()->create([
            'name' => 'Grace Hopper',
            'email' => 'grace@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'grace@example.com',
        ]);

        $row = DB::table('users')->where('id', $user->id)->first();

        $this->assertNotNull($row);
        $this->assertSame('Grace Hopper', $row->name);
    }

    public function test_email_uniqueness_is_enforced(): void
    {
        User::factory()->create([
            'email' => 'unique@example.com',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create([
            'email' => 'unique@example.com',
        ]);
    }
}
