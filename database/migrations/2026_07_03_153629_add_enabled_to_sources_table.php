<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add columns (enabled defaults to true)
        Schema::table('sources', function (Blueprint $table) {
            $table->boolean('enabled')->default(true)->after('link');
        });

        // Step 2: enable all sources for existing records
        DB::table('sources')->update(['enabled' => true]);
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropColumn('enabled');
        });
    }
};
