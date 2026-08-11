<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_count_shelves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('shelf_id')->constrained('shelves')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['stock_count_id', 'shelf_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_shelves');
    }
};
