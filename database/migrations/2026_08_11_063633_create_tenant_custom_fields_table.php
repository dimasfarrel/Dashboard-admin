<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Display name: "Nama Panggilan"
            $table->string('field_key')->unique(); // Slug key: "nama_panggilan"
            $table->string('type')->default('text'); // text, number, date, textarea
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('placeholder')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_custom_fields');
    }
};
