<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lodgings', function (Blueprint $table) {
            $table->decimal('daily_discount', 12, 2)->default(0)->after('discount');     // diskon per malam
            $table->decimal('fixed_discount', 12, 2)->default(0)->after('daily_discount'); // diskon flat total
            $table->string('payment_method', 50)->nullable()->after('payment_status');   // tunai/transfer/qris/lain-lain
        });
    }

    public function down(): void
    {
        Schema::table('lodgings', function (Blueprint $table) {
            $table->dropColumn(['daily_discount', 'fixed_discount', 'payment_method']);
        });
    }
};
