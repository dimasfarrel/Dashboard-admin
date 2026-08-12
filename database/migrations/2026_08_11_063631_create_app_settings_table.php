<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed default settings
        DB::table('app_settings')->insert([
            ['key' => 'payment_due_day', 'value' => '10', 'description' => 'Hari jatuh tempo pembayaran sewa (1-28)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'lodging_default_price', 'value' => '150000', 'description' => 'Harga default per malam penginapan (Rp)', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
