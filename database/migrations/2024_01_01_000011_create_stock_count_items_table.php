<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            // Sayım başladığı andaki beklenen miktar (üründeki değer değişse bile sabit kalır)
            $table->unsignedInteger('expected_quantity');

            // Her barkod okutması AYRI bir satır olarak kaydedilir (kural: girildiği
            // sırayla denetlenebilsin diye). Bu alan o TEK okutmadaki miktardır,
            // toplam değil — toplama yalnızca raporlama katmanında yapılır.
            $table->unsignedInteger('counted_quantity')->default(1);

            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('counted_at')->nullable();

            $table->timestamps();

            // NOT: Kasıtlı olarak (stock_count_id, product_id) üzerinde unique
            // constraint YOK — aynı ürün defalarca okutulabilir, her okutma ayrı
            // satır. Sorgu performansı için normal bir index yeterli.
            $table->index(['stock_count_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
    }
};