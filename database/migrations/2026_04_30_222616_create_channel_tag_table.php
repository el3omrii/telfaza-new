<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table for the many-to-many relationship between channels and tags.
     * Renamed from channels_tags to channel_tag following Laravel conventions.
     */
    public function up(): void
    {
        Schema::create('channel_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id');
            $table->unsignedBigInteger('tag_id');
 
            $table->primary(['channel_id', 'tag_id']);
 
            $table->foreign('channel_id')
                  ->references('id')
                  ->on('channels')
                  ->cascadeOnDelete();
 
            $table->foreign('tag_id')
                  ->references('id')
                  ->on('tags')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_tag');
    }
};
