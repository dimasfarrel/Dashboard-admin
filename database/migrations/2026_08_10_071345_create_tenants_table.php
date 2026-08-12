<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('nik', 16)->unique();
            $table->string('phone_wa', 20);
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('occupation')->nullable(); // Pekerjaan
            $table->string('origin_city')->nullable(); // Kota asal
            $table->enum('gender', ['laki-laki', 'perempuan'])->nullable();
            $table->date('birth_date')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('ktp_photo')->nullable(); // path foto KTP
            $table->string('selfie_photo')->nullable(); // foto selfie dengan KTP
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
