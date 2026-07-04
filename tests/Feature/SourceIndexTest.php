<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_sources_index_can_filter_by_channel_name_and_link(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $newsChannel = Channel::create(['name' => 'News Channel']);
        $sportsChannel = Channel::create(['name' => 'Sports Channel']);

        Source::create([
            'channel_id' => $newsChannel->id,
            'type' => 'hls',
            'link' => 'https://example.com/news.m3u8',
            'enabled' => true,
        ]);

        Source::create([
            'channel_id' => $sportsChannel->id,
            'type' => 'dash',
            'link' => 'https://example.com/sports.mpd',
            'enabled' => false,
        ]);

        $response = $this->get(route('sources.index', ['channel' => 'news', 'link' => 'example.com']));

        $response->assertOk();
        $response->assertSee('News Channel');
        $response->assertDontSee('Sports Channel');
        $response->assertSee('https://example.com/news.m3u8');
    }
}
