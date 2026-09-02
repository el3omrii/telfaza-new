<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Channel;
use App\Models\Report;
use App\Models\Source;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_see_the_dashboard_with_panel_stats(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $channel = Channel::create(['name' => 'News Channel', 'slug' => 'news-channel', 'views' => 120]);
        Source::create(['channel_id' => $channel->id, 'type' => 'hls', 'link' => 'https://example.com/news.m3u8', 'enabled' => true]);
        Category::create(['name' => 'Sports', 'slug' => 'sports']);
        Tag::create(['name' => 'football', 'slug' => 'football']);
        Report::create(['channel_id' => $channel->id, 'issue_type' => 'dead_stream', 'user_token' => 'token-1']);

        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertViewHas('channels', 1);
        $response->assertViewHas('sources', 1);
        $response->assertViewHas('categories', 1);
        $response->assertViewHas('tags', 1);
        $response->assertViewHas('totalViews', 120);
        $response->assertViewHas('pendingReports', 1);
    }

    public function test_login_redirects_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }
}
