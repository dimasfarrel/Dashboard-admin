<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lodgings', function (Blueprint $table) {
            $table->decimal('discount', 12, 2)->default(0)->after('deposit');
            $table->decimal('custom_adjustment', 12, 2)->default(0)->after('discount'); // korting harga manual
        });
    }

    public function down(): void
    {
        Schema::table('lodgings', function (Blueprint $table) {
            $table->dropColumn(['discount', 'custom_adjustment']);
        });
    }
};
