<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shelves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();

            // Depo içinde benzersiz olması gereken raf ID'si.
            // Sayısal (101), alfanümerik (A12) veya harf-sayı-tire (RAF-A-01) olabilir -> string.
            $table->string('shelf_code');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['warehouse_id', 'shelf_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shelves');
    }
};
