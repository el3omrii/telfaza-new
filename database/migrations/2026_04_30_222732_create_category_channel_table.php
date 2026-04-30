<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot table for the many-to-many relationship between channels and categories.
     * Renamed from channels_categories to category_channel following Laravel conventions.
     */
    public function up(): void
    {
        Schema::create('category_channel', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('channel_id');
 
            $table->primary(['category_id', 'channel_id']);
 
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->cascadeOnDelete();
 
            $table->foreign('channel_id')
                  ->references('id')
                  ->on('channels')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_channel');
    }
};
