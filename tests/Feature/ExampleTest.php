<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_root_route_redirects_to_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get('/');

        $response->assertRedirect('/dashboard');
    }
}
