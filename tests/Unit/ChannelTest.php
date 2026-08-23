<?php

namespace Tests\Unit;

use App\Models\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_incrementing_views_does_not_revalidate_nextjs_cache(): void
    {
        $channel = Channel::withoutEvents(function () {
            return Channel::create([
                'name' => 'Test channel',
                'slug' => 'test-channel',
                'views' => 0,
            ]);
        });

        Http::fake();

        $channel->incrementViews();

        $this->assertDatabaseHas('channels', [
            'id' => $channel->id,
            'views' => 1,
        ]);
        Http::assertNothingSent();
    }
}