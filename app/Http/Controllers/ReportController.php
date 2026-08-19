<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\OtherIncome;
use App\Models\Lodging;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Expense;
use App\Models\TenantDeposit;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Laporan Omzet Total — Hanya pendapatan operasional riil.
     * TIDAK termasuk: Hutang masuk, Deposit penyewa.
     */
    public function totalOmzet(Request $request)
    {
        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->toDateString();

        $incomes = collect();

        // 1. Sewa Kost (Payment status=paid)
        $payments = Payment::where('status', 'paid')
            ->where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->with(['tenant', 'room'])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->paid_at)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Sewa Kost',
                    'description' => "Kamar " . ($item->room->room_number ?? 'N/A') . " (" . ($item->tenant->name ?? 'N/A') . ")",
                    'amount' => (float) $item->amount,
                    'url' => route('payments.show', $item->id),
                ];
            });
        $incomes = $incomes->concat($payments);

        // 2. Penginapan (Lodging status=paid)
        $lodgings = Lodging::where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->whereMonth('paid_at', $currentMonth)
            ->whereYear('paid_at', $currentYear)
            ->with('room')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->paid_at)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Penginapan',
                    'description' => "Harian - Kamar " . ($item->room->room_number ?? 'N/A') . " (" . $item->pic_name . ")",
                    'amount' => (float) $item->calculateTotal(),
                    'url' => route('lodgings.show', $item->id),
                ];
            });
        $incomes = $incomes->concat($lodgings);

        // 3. Pendapatan Lain (OtherIncome)
        $otherIncomes = OtherIncome::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->income_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Lain-lain',
                    'description' => $item->title,
                    'amount' => (float) $item->amount,
                    'url' => route('other-incomes.show', $item->id),
                ];
            });
        $incomes = $incomes->concat($otherIncomes);

        // 4. Pelunasan Piutang (uang kembali masuk ke kas)
        $receivableRepayments = LoanRepayment::where('type', 'receivable')
            ->whereMonth('repayment_date', $currentMonth)
            ->whereYear('repayment_date', $currentYear)
            ->with('loan')
            ->get()
            ->map(function ($item) {
                $desc = "Pelunasan Piutang" . ($item->loan ? " (" . $item->loan->name . ")" : "");
                return [
                    'date' => Carbon::parse($item->repayment_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pelunasan Piutang',
                    'description' => $desc,
                    'amount' => (float) $item->amount,
                    'url' => $item->loan_id ? route('receivables.show', $item->loan_id) : '#',
                ];
            });
        $incomes = $incomes->concat($receivableRepayments);

        // Sort
        $incomes = $incomes->sortBy(function ($item) {
            return sprintf('%010d_%010d', $item['date']->timestamp, $item['created_at'] ? $item['created_at']->timestamp : 0);
        })->values();

        return view('reports.total_omzet', compact('incomes', 'startDate', 'endDate', 'currentMonth', 'currentYear'));
    }

    /**
     * Laporan Pengeluaran Total — Hanya beban operasional riil.
     * TIDAK termasuk: Piutang keluar, Pelunasan hutang.
     */
    public function totalPengeluaran(Request $request)
    {
        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->toDateString();

        $expenses = collect();

        // 1. Pengeluaran Kost (Expense) — sudah termasuk maintenance via syncExpense
        $expenseItems = Expense::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->expense_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pengeluaran',
                    'description' => $item->title,
                    'amount' => (float) $item->amount,
                    'url' => route('expenses.show', $item->id),
                ];
            });
        $expenses = $expenses->concat($expenseItems);

        // 2. Pengembalian Deposit (uang keluar dari kas)
        $depositDeductions = TenantDeposit::where('type', 'debit')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->with('tenant')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pengembalian Deposit',
                    'description' => "Pengembalian Deposit - " . ($item->tenant->name ?? 'N/A') . ($item->description ? " ({$item->description})" : ""),
                    'amount' => (float) $item->amount,
                    'url' => $item->tenant_id ? route('tenants.show', $item->tenant_id) : '#',
                ];
            });
        $expenses = $expenses->concat($depositDeductions);

        // Sort
        $expenses = $expenses->sortBy(function ($item) {
            return sprintf('%010d_%010d', $item['date']->timestamp, $item['created_at'] ? $item['created_at']->timestamp : 0);
        })->values();

        return view('reports.total_pengeluaran', compact('expenses', 'startDate', 'endDate', 'currentMonth', 'currentYear'));
    }

    /**
     * Laporan Arus Kas — Seluruh pergerakan uang masuk & keluar.
     * Menggantikan laporan hutang/piutang yang sebelumnya terlalu sempit.
     */
    public function cashFlow(Request $request)
    {
        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->toDateString();

        $transactions = collect();

        // =====================
        // KAS MASUK
        // =====================

        // 1. Sewa Kost
        $payments = Payment::where('status', 'paid')
            ->where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->with(['tenant', 'room'])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->paid_at)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Sewa Kost',
                    'description' => "Kamar " . ($item->room->room_number ?? 'N/A') . " (" . ($item->tenant->name ?? 'N/A') . ")",
                    'kas_masuk' => (float) $item->amount,
                    'kas_keluar' => 0,
                    'url' => $item->loan_id ? route('receivables.show', $item->loan_id) : '#',
                    'url' => route('lodgings.show', $item->id),
                    'url' => route('payments.show', $item->id),
                    'url' => route('payments.show', $item->id),
                ];
            });
        $transactions = $transactions->concat($payments);

        // 2. Penginapan
        $lodgings = Lodging::where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->whereMonth('paid_at', $currentMonth)
            ->whereYear('paid_at', $currentYear)
            ->with('room')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->paid_at)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Penginapan',
                    'description' => "Harian - Kamar " . ($item->room->room_number ?? 'N/A') . " (" . $item->pic_name . ")",
                    'kas_masuk' => (float) $item->calculateTotal(),
                    'kas_keluar' => 0,
                    'url' => route('lodgings.show', $item->id),
                ];
            });
        $transactions = $transactions->concat($lodgings);

        // 3. Pendapatan Lain
        $otherIncomes = OtherIncome::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->income_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pendapatan Lain',
                    'description' => $item->title,
                    'kas_masuk' => (float) $item->amount,
                    'kas_keluar' => 0,
                    'url' => route('other-incomes.show', $item->id),
                ];
            });
        $transactions = $transactions->concat($otherIncomes);

        // 4. Pelunasan Piutang (uang kembali masuk)
        $receivableRepayments = LoanRepayment::where('type', 'receivable')
            ->whereMonth('repayment_date', $currentMonth)
            ->whereYear('repayment_date', $currentYear)
            ->with('loan')
            ->get()
            ->map(function ($item) {
                $desc = "Pelunasan Piutang" . ($item->loan ? " (" . $item->loan->name . ")" : "");
                return [
                    'date' => Carbon::parse($item->repayment_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pelunasan Piutang',
                    'description' => $desc,
                    'kas_masuk' => (float) $item->amount,
                    'kas_keluar' => 0,
                    'url' => $item->loan_id ? route('receivables.show', $item->loan_id) : '#',
                ];
            });
        $transactions = $transactions->concat($receivableRepayments);

        // 5. Hutang Masuk (pinjaman diterima → kas bertambah)
        $payables = Loan::where('type', 'payable')
            ->whereMonth('loan_date', $currentMonth)
            ->whereYear('loan_date', $currentYear)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->loan_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Hutang Masuk',
                    'description' => "Pinjaman dari " . $item->name,
                    'kas_masuk' => (float) $item->total_amount,
                    'kas_keluar' => 0,
                    'url' => route('payables.show', $item->id),
                ];
            });
        $transactions = $transactions->concat($payables);

        // 6. Deposit Masuk (titipan dari penyewa → kas bertambah)
        $depositCredits = TenantDeposit::where('type', 'credit')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->with('tenant')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Deposit Masuk',
                    'description' => "Deposit - " . ($item->tenant->name ?? 'N/A') . ($item->description ? " ({$item->description})" : ""),
                    'kas_masuk' => (float) $item->amount,
                    'kas_keluar' => 0,
                    'url' => $item->tenant_id ? route('tenants.show', $item->tenant_id) : '#',
                ];
            });
        $transactions = $transactions->concat($depositCredits);

        // =====================
        // KAS KELUAR
        // =====================

        // 7. Pengeluaran Kost (termasuk maintenance via syncExpense)
        $expenseItems = Expense::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->expense_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pengeluaran',
                    'description' => $item->title,
                    'kas_masuk' => 0,
                    'kas_keluar' => (float) $item->amount,
                    'url' => $item->tenant_id ? route('tenants.show', $item->tenant_id) : '#',
                    'url' => route('expenses.show', $item->id),
                    'url' => route('expenses.show', $item->id),
                ];
            });
        $transactions = $transactions->concat($expenseItems);

        // 8. Piutang Keluar (uang dipinjamkan → kas berkurang)
        $receivables = Loan::where('type', 'receivable')
            ->whereMonth('loan_date', $currentMonth)
            ->whereYear('loan_date', $currentYear)
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->loan_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Piutang Keluar',
                    'description' => "Pinjaman ke " . $item->name,
                    'kas_masuk' => 0,
                    'kas_keluar' => (float) $item->total_amount,
                    'url' => route('receivables.show', $item->id),
                ];
            });
        $transactions = $transactions->concat($receivables);

        // 9. Pelunasan Hutang (bayar hutang → kas berkurang)
        $payableRepayments = LoanRepayment::where('type', 'payable')
            ->whereMonth('repayment_date', $currentMonth)
            ->whereYear('repayment_date', $currentYear)
            ->with('loan')
            ->get()
            ->map(function ($item) {
                $desc = "Pelunasan Hutang" . ($item->loan ? " (" . $item->loan->name . ")" : "");
                return [
                    'date' => Carbon::parse($item->repayment_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pelunasan Hutang',
                    'description' => $desc,
                    'kas_masuk' => 0,
                    'kas_keluar' => (float) $item->amount,
                    'url' => $item->loan_id ? route('payables.show', $item->loan_id) : '#',
                ];
            });
        $transactions = $transactions->concat($payableRepayments);

        // 10. Pengembalian Deposit (kas berkurang)
        $depositDebits = TenantDeposit::where('type', 'debit')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->with('tenant')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pengembalian Deposit',
                    'description' => "Pengembalian Deposit - " . ($item->tenant->name ?? 'N/A') . ($item->description ? " ({$item->description})" : ""),
                    'kas_masuk' => 0,
                    'kas_keluar' => (float) $item->amount,
                    'url' => $item->tenant_id ? route('tenants.show', $item->tenant_id) : '#',
                ];
            });
        $transactions = $transactions->concat($depositDebits);

        // Sort by date
        $transactions = $transactions->sortBy(function ($item) {
            return sprintf('%010d_%010d', $item['date']->timestamp, $item['created_at'] ? $item['created_at']->timestamp : 0);
        })->values();

        // Totals
        $totalKasMasuk = $transactions->sum('kas_masuk');
        $totalKasKeluar = $transactions->sum('kas_keluar');
        $saldoBersih = $totalKasMasuk - $totalKasKeluar;

        return view('reports.cash_flow', compact(
            'transactions', 'startDate', 'endDate', 'currentMonth', 'currentYear',
            'totalKasMasuk', 'totalKasKeluar', 'saldoBersih'
        ));
    }
}
