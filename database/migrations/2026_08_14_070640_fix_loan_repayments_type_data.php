<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix mismatched types in loan_repayments where type defaults to receivable
        // but the associated loan is payable
        $repayments = DB::table('loan_repayments')->get();
        foreach ($repayments as $rep) {
            if ($rep->loan_id) {
                $loan = DB::table('loans')->where('id', $rep->loan_id)->first();
                if ($loan && $rep->type !== $loan->type) {
                    DB::table('loan_repayments')
                        ->where('id', $rep->id)
                        ->update(['type' => $loan->type]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for data fix
    }
};
