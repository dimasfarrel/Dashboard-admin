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
        Schema::table('loans', function (Blueprint $table) {
            $table->string('type')->default('receivable')->after('id');
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->string('type')->default('receivable')->after('loan_id');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('loan_repayments', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
