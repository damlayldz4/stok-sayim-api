<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);

            // Barkod okutulurken girilen miktar bu değeri aşarsa mobil
            // uygulama önce onay ister (danışman notu: "girilen sayı belli
            // bir değerden büyükse uyarı verip onay alsın, bu sayıyı da
            // şirket admini belirlesin"). Null = uyarı kapalı.
            $table->unsignedInteger('scan_quantity_warning_threshold')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};