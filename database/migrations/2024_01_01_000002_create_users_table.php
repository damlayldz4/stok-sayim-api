<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Sistem yöneticisi için company_id null olur.
            $table->foreignId('company_id')->nullable()
                ->constrained('companies')->nullOnDelete();

            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');

            // system_admin | company_admin | count_staff
            $table->enum('role', ['system_admin', 'company_admin', 'count_staff']);

            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
