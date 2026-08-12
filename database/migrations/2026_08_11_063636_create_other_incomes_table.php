<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('title');                    // Judul/keterangan
            $table->string('category')->default('lain-lain'); // Kategori: parkir, laundry, dll
            $table->decimal('amount', 12, 2);
            $table->date('income_date');
            $table->integer('period_month');
            $table->integer('period_year');
            $table->string('receipt_photo')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_incomes');
    }
};
