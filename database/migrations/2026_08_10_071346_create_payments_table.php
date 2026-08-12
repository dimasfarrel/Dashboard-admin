<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->integer('period_month'); // 1-12
            $table->integer('period_year');
            $table->date('paid_at')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['paid', 'pending', 'overdue'])->default('pending');
            $table->enum('payment_method', ['tunai', 'transfer', 'qris', 'lain-lain'])->nullable();
            $table->string('receipt_photo')->nullable(); // bukti bayar
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
