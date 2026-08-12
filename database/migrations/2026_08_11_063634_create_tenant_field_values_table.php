<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('field_key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_field_values');
    }
};
