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
        Schema::create('cat_thirds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('cat_second_id')->constrained('cat_seconds')->cascadeOnDelete();
            $table->foreignId('cat_first_id')->constrained('cat_firsts')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_thirds');
    }
};
