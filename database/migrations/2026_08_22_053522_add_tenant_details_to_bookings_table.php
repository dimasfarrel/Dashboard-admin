<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('nik', 16)->after('room_id');
            $table->string('phone_wa', 20)->after('nik');
            $table->enum('gender', ['laki-laki', 'perempuan'])->nullable()->after('phone_wa');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['nik', 'phone_wa', 'gender']);
        });
    }
};
