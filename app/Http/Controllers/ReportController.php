<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\OtherIncome;
use App\Models\Lodging;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Expense;
use App\Models\RoomMaintenance;
use App\Models\TenantDeposit;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function totalOmzet(Request $request)
    {
        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->toDateString();

        $incomes = collect();

        // 1. Omzet Kost (Payment)
        $payments = Payment::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->with(['tenant', 'room'])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->paid_at)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Sewa Kost',
                    'description' => "Kamar " . ($item->room->room_number ?? 'N/A') . " (" . ($item->tenant->name ?? 'N/A') . ")",
                    'omzet_amount' => (float) $item->amount,
                    'hutang_amount' => 0,
                    'deposit_amount' => 0,
                ];
            });
        $incomes = $incomes->concat($payments);

        // 2. Penginapan (Lodging)
        $lodgings = Lodging::where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->with('room')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->paid_at)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Penginapan',
                    'description' => "Harian - Kamar " . ($item->room->room_number ?? 'N/A') . " (" . $item->pic_name . ")",
                    'omzet_amount' => (float) $item->calculateTotal(),
                    'hutang_amount' => 0,
                    'deposit_amount' => (float) $item->deposit,
                ];
            });
        $incomes = $incomes->concat($lodgings);

        // 3. Pendapatan Lain (OtherIncome)
        $otherIncomes = OtherIncome::whereBetween('income_date', [$startDate, $endDate])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->income_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Lain-lain',
                    'description' => $item->title,
                    'omzet_amount' => (float) $item->amount,
                    'hutang_amount' => 0,
                    'deposit_amount' => 0,
                ];
            });
        $incomes = $incomes->concat($otherIncomes);

        // 4. Pelunasan Piutang (Masuk ke Omzet karena nambah kas)
        $receivableRepayments = LoanRepayment::where('type', 'receivable')
            ->whereBetween('repayment_date', [$startDate, $endDate])
            ->with('loan')
            ->get()
            ->map(function ($item) {
                $desc = "Pelunasan Piutang" . ($item->loan ? " (" . $item->loan->name . ")" : "");
                return [
                    'date' => Carbon::parse($item->repayment_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pelunasan',
                    'description' => $desc,
                    'omzet_amount' => (float) $item->amount,
                    'hutang_amount' => 0,
                    'deposit_amount' => 0,
                ];
            });
        $incomes = $incomes->concat($receivableRepayments);

        // 5. Hutang (Loan type='payable') -> uang masuk
        $payables = Loan::where('type', 'payable')
            ->whereBetween('loan_date', [$startDate, $endDate])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->loan_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Hutang Masuk',
                    'description' => "Pinjaman dari " . $item->name,
                    'omzet_amount' => 0,
                    'hutang_amount' => (float) $item->total_amount,
                    'deposit_amount' => 0,
                ];
            });
        $incomes = $incomes->concat($payables);

        // 6. Deposit Penyewa (TenantDeposit type='credit')
        $tenantDeposits = TenantDeposit::where('type', 'credit')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('tenant')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Deposit Masuk',
                    'description' => "Deposit - " . ($item->tenant->name ?? 'N/A') . ($item->description ? " ({$item->description})" : ""),
                    'omzet_amount' => 0,
                    'hutang_amount' => 0,
                    'deposit_amount' => (float) $item->amount,
                ];
            });
        $incomes = $incomes->concat($tenantDeposits);

        // Sort Pemasukan
        $incomes = $incomes->sortBy(function ($item) {
            return sprintf('%010d_%010d', $item['date']->timestamp, $item['created_at'] ? $item['created_at']->timestamp : 0);
        })->values();

        return view('reports.total_omzet', compact('incomes', 'startDate', 'endDate', 'currentMonth', 'currentYear'));
    }

    public function totalPengeluaran(Request $request)
    {
        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->toDateString();

        $expenses = collect();

        // 1. Pengeluaran Kost (Expense)
        $expenseItems = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->expense_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pengeluaran',
                    'description' => $item->title,
                    'pengeluaran_amount' => (float) $item->amount,
                    'piutang_amount' => 0,
                ];
            });
        $expenses = $expenses->concat($expenseItems);

        // 3. Pembayaran Hutang (Pelunasan Hutang keluar kas)
        $payableRepayments = LoanRepayment::where('type', 'payable')
            ->whereBetween('repayment_date', [$startDate, $endDate])
            ->with('loan')
            ->get()
            ->map(function ($item) {
                $desc = "Pelunasan Hutang" . ($item->loan ? " (" . $item->loan->name . ")" : "");
                return [
                    'date' => Carbon::parse($item->repayment_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pelunasan',
                    'description' => $desc,
                    'pengeluaran_amount' => (float) $item->amount,
                    'piutang_amount' => 0,
                ];
            });
        $expenses = $expenses->concat($payableRepayments);

        // 4. Piutang (Loan type='receivable') -> uang keluar
        $receivables = Loan::where('type', 'receivable')
            ->whereBetween('loan_date', [$startDate, $endDate])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->loan_date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Piutang Keluar',
                    'description' => "Pinjaman ke " . $item->name,
                    'pengeluaran_amount' => 0,
                    'piutang_amount' => (float) $item->total_amount,
                ];
            });
        $expenses = $expenses->concat($receivables);

        // 5. Pengembalian Deposit (TenantDeposit type='debit') -> uang keluar
        $depositDeductions = \App\Models\TenantDeposit::where('type', 'debit')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('tenant')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->startOfDay(),
                    'created_at' => $item->created_at,
                    'category' => 'Pengembalian Deposit',
                    'description' => "Pengembalian Deposit - " . ($item->tenant->name ?? 'N/A') . ($item->description ? " ({$item->description})" : ""),
                    'pengeluaran_amount' => (float) $item->amount,
                    'piutang_amount' => 0,
                ];
            });
        $expenses = $expenses->concat($depositDeductions);

        // Sort Pengeluaran
        $expenses = $expenses->sortBy(function ($item) {
            return sprintf('%010d_%010d', $item['date']->timestamp, $item['created_at'] ? $item['created_at']->timestamp : 0);
        })->values();

        return view('reports.total_pengeluaran', compact('expenses', 'startDate', 'endDate', 'currentMonth', 'currentYear'));
    }

    public function loans(Request $request)
    {
        $currentMonth = $request->input('month', Carbon::now()->month);
        $currentYear = $request->input('year', Carbon::now()->year);
        
        $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->toDateString();
        
        $typeFilter = $request->input('type', 'all'); // 'all', 'receivable', 'payable'

        $transactions = collect();

        // Pemasukan: Pelunasan Piutang (LoanRepayment receivable)
        if (in_array($typeFilter, ['all', 'receivable'])) {
            $receivableRepayments = LoanRepayment::where('type', 'receivable')
                ->whereBetween('repayment_date', [$startDate, $endDate])
                ->with('loan')
                ->get()
                ->map(function ($item) {
                    $desc = "Pelunasan Piutang";
                    if ($item->loan) {
                        $desc .= " (" . $item->loan->name . ")";
                    }
                    return [
                        'date' => Carbon::parse($item->repayment_date)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Pelunasan Piutang',
                        'description' => $desc,
                        'notes' => $item->notes,
                        'type' => 'receivable', // Masuk
                        'amount' => (float) $item->amount,
                        'route' => $item->loan ? route('receivables.show', $item->loan_id) : route('receivables.index')
                    ];
                });
            $transactions = $transactions->concat($receivableRepayments);
        }

        // Pengeluaran: Pembayaran Hutang (LoanRepayment payable)
        if (in_array($typeFilter, ['all', 'payable'])) {
            $payableRepayments = LoanRepayment::where('type', 'payable')
                ->whereBetween('repayment_date', [$startDate, $endDate])
                ->with('loan')
                ->get()
                ->map(function ($item) {
                    $desc = "Pembayaran Hutang";
                    if ($item->loan) {
                        $desc .= " (" . $item->loan->name . ")";
                    }
                    return [
                        'date' => Carbon::parse($item->repayment_date)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Pembayaran Hutang',
                        'description' => $desc,
                        'notes' => $item->notes,
                        'type' => 'payable', // Keluar
                        'amount' => (float) $item->amount,
                        'route' => $item->loan ? route('payables.show', $item->loan_id) : route('payables.index')
                    ];
                });
            $transactions = $transactions->concat($payableRepayments);
        }

        // Sort by date descending, then by created_at descending
        $transactions = $transactions->sortByDesc(function ($item) {
            return sprintf('%010d_%010d', $item['date']->timestamp, $item['created_at'] ? $item['created_at']->timestamp : 0);
        })->values();

        // Calculate Totals
        $totalReceivable = $transactions->where('type', 'receivable')->sum('amount');
        $totalPayable = $transactions->where('type', 'payable')->sum('amount');
        $netCashflow = $totalReceivable - $totalPayable;

        return view('reports.loans', compact(
            'transactions', 'startDate', 'endDate', 'typeFilter',
            'totalReceivable', 'totalPayable', 'netCashflow', 'currentMonth', 'currentYear'
        ));
    }
}
