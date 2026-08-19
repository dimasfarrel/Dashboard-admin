<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('emergency_contact_name_2')->nullable()->after('emergency_contact_phone');
            $table->string('emergency_contact_phone_2')->nullable()->after('emergency_contact_name_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['emergency_contact_name_2', 'emergency_contact_phone_2']);
        });
    }
};
