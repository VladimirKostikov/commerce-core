<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Application is running.', false);
    }

    public function test_framework_health_route_is_up(): void
    {
        $this->get('/up')->assertOk();
    }
}
