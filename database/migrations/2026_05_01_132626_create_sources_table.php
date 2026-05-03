<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['hls', 'dash', 'embed'])->default('hls');
            $table->string('link')->nullable();
            $table->boolean('drm')->default(false);
            $table->text('clearkeys')->nullable();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
