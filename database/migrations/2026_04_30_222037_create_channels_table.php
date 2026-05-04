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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->mediumText('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('image')->nullable();
            $table->string('language')->nullable();
            $table->string('quality')->nullable();
            $table->string('epgid')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->timestamps();
 
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
