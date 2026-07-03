<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourcesToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_can_be_toggled_enabled_status(): void
    {
        $user = User::factory()->create();
        $channel = Channel::create([
            'name' => 'Test Channel',
            'slug' => 'test-channel',
        ]);
        $source = Source::create([
            'channel_id' => $channel->id,
            'type' => 'hls',
            'link' => 'https://example.com/stream.m3u8',
            'enabled' => false,
        ]);

        $this->actingAs($user)
            ->post(route('sources.toggle', $source), ['enabled' => true])
            ->assertRedirect();

        $this->assertDatabaseHas('sources', [
            'id' => $source->id,
            'enabled' => true,
        ]);

        $this->actingAs($user)
            ->post(route('sources.toggle', $source), ['enabled' => false])
            ->assertRedirect();

        $this->assertDatabaseHas('sources', [
            'id' => $source->id,
            'enabled' => false,
        ]);
    }
}
