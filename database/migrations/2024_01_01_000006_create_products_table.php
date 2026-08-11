<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            $table->string('product_name');
            $table->string('product_code')->nullable();

            // Barkod metin olarak saklanır ki başındaki sıfırlar korunsun.
            $table->string('barcode');

            $table->unsignedInteger('expected_quantity')->default(0);

            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('shelf_id')->nullable()->constrained('shelves')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Aynı şirket içinde aynı barkodla ikinci ürün oluşturulamaz.
            $table->unique(['company_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
