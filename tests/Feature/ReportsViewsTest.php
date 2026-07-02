<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_pages_are_accessible(): void
    {
        $user = User::factory()->create();
        $channel = Channel::create([
            'name' => 'Test Channel',
            'slug' => 'test-channel',
        ]);
        $report = Report::create([
            'channel_id' => $channel->id,
            'issue_type' => 'dead_stream',
            'details' => 'The stream is not available.',
            'user_agent' => 'Mozilla/5.0',
        ]);

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertViewIs('reports.index');

        $this->actingAs($user)
            ->get(route('reports.edit', $report))
            ->assertOk()
            ->assertViewIs('reports.edit');
    }
}
