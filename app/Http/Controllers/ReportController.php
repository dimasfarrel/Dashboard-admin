<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\OtherIncome;
use App\Models\Lodging;
use App\Models\LoanRepayment;
use App\Models\Expense;
use App\Models\RoomMaintenance;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $typeFilter = $request->input('type', 'all'); // 'all', 'income', 'expense'

        $transactions = collect();

        // 1. Pemasukan: Pembayaran Kost (Payment)
        if (in_array($typeFilter, ['all', 'income'])) {
            $payments = Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->with(['tenant', 'room'])
                ->get()
                ->map(function ($item) {
                    $desc = "Pembayaran Kost - Kamar " . ($item->room->room_number ?? 'N/A') . " (" . ($item->tenant->name ?? 'N/A') . ")";
                    if ($item->period_month && $item->period_year) {
                        $desc .= " periode " . $item->period_label;
                    }
                    return [
                        'date' => Carbon::parse($item->paid_at)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Omzet Kost',
                        'description' => $desc,
                        'notes' => $item->notes,
                        'type' => 'income',
                        'amount' => (float) $item->amount,
                        'route' => route('payments.show', $item->id)
                    ];
                });
            $transactions = $transactions->concat($payments);

            // 2. Pemasukan: Pendapatan Lain (OtherIncome)
            $otherIncomes = OtherIncome::whereBetween('income_date', [$startDate, $endDate])
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->income_date)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Pendapatan Lain',
                        'description' => $item->title,
                        'notes' => $item->notes,
                        'type' => 'income',
                        'amount' => (float) $item->amount,
                        'route' => route('other-incomes.index')
                    ];
                });
            $transactions = $transactions->concat($otherIncomes);

            // 3. Pemasukan: Penginapan (Lodging)
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
                        'description' => "Sewa Harian - Kamar " . ($item->room->room_number ?? 'N/A') . " (" . $item->pic_name . ")",
                        'notes' => $item->notes,
                        'type' => 'income',
                        'amount' => (float) $item->calculateTotal(),
                        'route' => route('lodgings.show', $item->id)
                    ];
                });
            $transactions = $transactions->concat($lodgings);
        }

        // 4. Pengeluaran: Pengeluaran Kost (Expense)
        if (in_array($typeFilter, ['all', 'expense'])) {
            $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->expense_date)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Pengeluaran Kost',
                        'description' => $item->title,
                        'notes' => $item->notes,
                        'type' => 'expense',
                        'amount' => (float) $item->amount,
                        'route' => route('expenses.index')
                    ];
                });
            $transactions = $transactions->concat($expenses);

            // 5. Pengeluaran: Maintenance Kamar (RoomMaintenance)
            $maintenances = RoomMaintenance::where('cost', '>', 0)
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('done_date', [$startDate, $endDate])
                      ->orWhere(function($subQ) use ($startDate, $endDate) {
                          $subQ->whereNull('done_date')
                               ->whereBetween('report_date', [$startDate, $endDate]);
                      });
                })
                ->with('room')
                ->get()
                ->map(function ($item) {
                    $date = $item->done_date ? $item->done_date : $item->report_date;
                    $desc = "Maintenance Kamar " . ($item->room->room_number ?? 'N/A') . " - " . $item->item_name;
                    return [
                        'date' => Carbon::parse($date)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Maintenance',
                        'description' => $desc,
                        'notes' => $item->notes,
                        'type' => 'expense',
                        'amount' => (float) $item->cost,
                        'route' => route('maintenances.index')
                    ];
                });
            $transactions = $transactions->concat($maintenances);
        }

        // Sort by date descending, then by created_at descending (newest first)
        $transactions = $transactions->sortByDesc(function ($item) {
            return sprintf('%010d_%010d', $item['date']->timestamp, $item['created_at'] ? $item['created_at']->timestamp : 0);
        })->values();

        // Calculate Totals
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netCashflow = $totalIncome - $totalExpense;

        return view('reports.index', compact(
            'transactions', 'startDate', 'endDate', 'typeFilter',
            'totalIncome', 'totalExpense', 'netCashflow'
        ));
    }

    public function loans(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
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
            'totalReceivable', 'totalPayable', 'netCashflow'
        ));
    }

    public function periods(Request $request)
    {
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $typeFilter = $request->input('type', 'all'); // 'all', 'income', 'expense'

        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $transactions = collect();

        // 1. Pemasukan: Pembayaran Kost (Payment)
        if (in_array($typeFilter, ['all', 'income'])) {
            $payments = Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->with(['tenant', 'room'])
                ->get()
                ->map(function ($item) {
                    $desc = "Pembayaran Kost - Kamar " . ($item->room->room_number ?? 'N/A') . " (" . ($item->tenant->name ?? 'N/A') . ")";
                    if ($item->period_month && $item->period_year) {
                        $desc .= " periode " . $item->period_label;
                    }
                    return [
                        'date' => Carbon::parse($item->paid_at)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Omzet Kost',
                        'description' => $desc,
                        'notes' => $item->notes,
                        'type' => 'income',
                        'amount' => (float) $item->amount,
                        'route' => route('payments.show', $item->id)
                    ];
                });
            $transactions = $transactions->concat($payments);

            // 2. Pemasukan: Pendapatan Lain (OtherIncome)
            $otherIncomes = OtherIncome::whereBetween('income_date', [$startDate, $endDate])
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->income_date)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Pendapatan Lain',
                        'description' => $item->title,
                        'notes' => $item->notes,
                        'type' => 'income',
                        'amount' => (float) $item->amount,
                        'route' => route('other-incomes.index')
                    ];
                });
            $transactions = $transactions->concat($otherIncomes);

            // 3. Pemasukan: Penginapan (Lodging)
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
                        'description' => "Sewa Harian - Kamar " . ($item->room->room_number ?? 'N/A') . " (" . $item->pic_name . ")",
                        'notes' => $item->notes,
                        'type' => 'income',
                        'amount' => (float) $item->calculateTotal(),
                        'route' => route('lodgings.show', $item->id)
                    ];
                });
            $transactions = $transactions->concat($lodgings);
        }

        // 4. Pengeluaran: Pengeluaran Kost (Expense)
        if (in_array($typeFilter, ['all', 'expense'])) {
            $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->expense_date)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Pengeluaran Kost',
                        'description' => $item->title,
                        'notes' => $item->notes,
                        'type' => 'expense',
                        'amount' => (float) $item->amount,
                        'route' => route('expenses.index')
                    ];
                });
            $transactions = $transactions->concat($expenses);

            // 5. Pengeluaran: Maintenance Kamar (RoomMaintenance)
            $maintenances = RoomMaintenance::where('cost', '>', 0)
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('done_date', [$startDate, $endDate])
                      ->orWhere(function($subQ) use ($startDate, $endDate) {
                          $subQ->whereNull('done_date')
                               ->whereBetween('report_date', [$startDate, $endDate]);
                      });
                })
                ->with('room')
                ->get()
                ->map(function ($item) {
                    $date = $item->done_date ? $item->done_date : $item->report_date;
                    $desc = "Maintenance Kamar " . ($item->room->room_number ?? 'N/A') . " - " . $item->item_name;
                    return [
                        'date' => Carbon::parse($date)->startOfDay(),
                        'created_at' => $item->created_at,
                        'category' => 'Maintenance',
                        'description' => $desc,
                        'notes' => $item->notes,
                        'type' => 'expense',
                        'amount' => (float) $item->cost,
                        'route' => route('maintenances.index')
                    ];
                });
            $transactions = $transactions->concat($maintenances);
        }

        // Sort by date descending, then by created_at descending (newest first)
        $transactions = $transactions->sortByDesc(function ($item) {
            return sprintf('%010d_%010d', $item['date']->timestamp, $item['created_at'] ? $item['created_at']->timestamp : 0);
        })->values();

        // Calculate Totals
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netCashflow = $totalIncome - $totalExpense;

        return view('reports.periods', compact(
            'transactions', 'month', 'year', 'typeFilter',
            'totalIncome', 'totalExpense', 'netCashflow'
        ));
    }
}
