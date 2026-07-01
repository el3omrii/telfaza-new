<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add columns (slug nullable first, so existing rows don't fail)
        Schema::table('tags', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Step 2: Backfill slugs for existing records
        $usedSlugs = [];

        DB::table('tags')->orderBy('id')->each(function ($tag) use (&$usedSlugs) {
            $base = Str::slug($tag->name);
            $slug = $base;
            $counter = 2;

            // Handle collisions
            while (in_array($slug, $usedSlugs)) {
                $slug = $base . '-' . $counter++;
            }

            $usedSlugs[] = $slug;
            DB::table('tags')->where('id', $tag->id)->update(['slug' => $slug]);
        });

        // Step 3: Now that all rows have a slug, enforce uniqueness and not-null
        Schema::table('tags', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
    }
};
