<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lodgings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('pic_name'); // nama penanggung jawab
            $table->string('pic_phone', 20); // no HP PIC
            $table->string('pic_nik', 16)->nullable(); // NIK PIC
            $table->string('pic_address')->nullable(); // alamat PIC
            $table->datetime('check_in');
            $table->datetime('check_out');
            $table->integer('duration_days')->default(1);
            $table->integer('guest_count')->default(1);
            $table->decimal('price_per_night', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->decimal('deposit', 12, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('guest_names')->nullable(); // nama tamu lainnya (JSON/text)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lodgings');
    }
};
