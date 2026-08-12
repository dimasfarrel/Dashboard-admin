<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->enum('category', [
                'elektronik',
                'kasur_furniture',
                'plumbing',
                'cat_dinding',
                'pintu_jendela',
                'ac_pendingin',
                'lain-lain'
            ]);
            $table->string('item_name'); // nama barang yang dirawat/diperbaiki
            $table->text('description');
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('vendor')->nullable(); // nama tukang/vendor
            $table->string('vendor_phone', 20)->nullable();
            $table->date('report_date');
            $table->date('done_date')->nullable();
            $table->string('before_photo')->nullable();
            $table->string('after_photo')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'done', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_maintenances');
    }
};
