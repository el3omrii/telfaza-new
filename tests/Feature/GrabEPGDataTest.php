<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GrabEPGDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_revalidates_slides_after_a_successful_epg_refresh(): void
    {
        config(['services.epg.base_url' => 'https://epg.test/guide']);
        putenv('NEXTJS_URL=https://next.test');
        putenv('NEXTJS_REVALIDATION_SECRET=test-secret');
        $_ENV['NEXTJS_URL'] = $_SERVER['NEXTJS_URL'] = 'https://next.test';
        $_ENV['NEXTJS_REVALIDATION_SECRET'] = $_SERVER['NEXTJS_REVALIDATION_SECRET'] = 'test-secret';

        DB::table('channels')->insert([
            'name' => 'Test channel',
            'slug' => 'test-channel',
            'epgid' => 'test-epg-id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'epg.test/*' => Http::response(['shows' => []]),
            'next.test/*' => Http::response(['success' => true]),
        ]);

        $this->artisan('app:grab-epg')->assertExitCode(0);

        Http::assertSent(fn (Request $request) =>
            $request->url() === 'https://next.test/api/revalidate'
            && $request->method() === 'POST'
            && $request['tags'] === ['slides']
            && $request->hasHeader('x-revalidate-secret', 'test-secret')
        );

        putenv('NEXTJS_URL');
        putenv('NEXTJS_REVALIDATION_SECRET');
        unset($_ENV['NEXTJS_URL'], $_SERVER['NEXTJS_URL']);
        unset($_ENV['NEXTJS_REVALIDATION_SECRET'], $_SERVER['NEXTJS_REVALIDATION_SECRET']);
    }

    public function test_it_does_not_revalidate_slides_when_no_epg_refresh_succeeds(): void
    {
        config(['services.epg.base_url' => 'https://epg.test/guide']);

        DB::table('channels')->insert([
            'name' => 'Test channel',
            'slug' => 'test-channel',
            'epgid' => 'test-epg-id',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            'epg.test/*' => Http::response([], 503),
        ]);

        $this->artisan('app:grab-epg')->assertExitCode(0);

        Http::assertNotSent(fn (Request $request) =>
            $request->url() === '/api/revalidate'
            || str_ends_with($request->url(), '/api/revalidate')
        );
    }
}
