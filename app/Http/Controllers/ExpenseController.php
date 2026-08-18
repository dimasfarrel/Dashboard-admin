<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = $request->month ?? now()->month;
        $currentYear  = $request->year  ?? now()->year;
        $selectedCategory = $request->filled('category') ? $request->category : null;

        // --- 1. Get Expenses ---
        $expensesQuery = Expense::query();
        if ($request->filled('month'))    $expensesQuery->where('period_month', $currentMonth);
        if ($request->filled('year'))     $expensesQuery->where('period_year', $currentYear);
        if ($selectedCategory && $selectedCategory !== 'deposit_deduction') {
            $expensesQuery->where('category', $selectedCategory);
        }
        $expensesList = $expensesQuery->get()->map(function($e) {
            return (object) [
                'id'            => $e->id,
                'is_deposit'    => false,
                'expense_date'  => $e->expense_date,
                'category'      => $e->category,
                'category_label'=> $e->category_label,
                'category_icon' => $e->category_icon,
                'title'         => $e->title,
                'description'   => $e->description,
                'period_month'  => $e->period_month,
                'period_year'   => $e->period_year,
                'amount'        => $e->amount,
                'notes'         => $e->notes,
                'receipt_photo' => $e->receipt_photo,
            ];
        });

        // --- 2. Get Deposit Deductions (Pengembalian/Potongan Deposit) ---
        $depositsList = collect([]);
        if (!$selectedCategory || $selectedCategory === 'deposit_deduction') {
            $depositsQuery = \App\Models\TenantDeposit::with('tenant.room')->where('type', 'debit');
            if ($request->filled('month')) $depositsQuery->whereMonth('date', $currentMonth);
            if ($request->filled('year'))  $depositsQuery->whereYear('date', $currentYear);
            
            $depositsList = $depositsQuery->get()->map(function($d) {
                return (object) [
                    'id'            => $d->id,
                    'is_deposit'    => true,
                    'expense_date'  => \Carbon\Carbon::parse($d->date)->startOfDay(),
                    'category'      => 'deposit_deduction',
                    'category_label'=> 'Pengembalian Deposit',
                    'category_icon' => 'bi-wallet2',
                    'title'         => 'Pengembalian / Potongan Deposit',
                    'description'   => ($d->tenant->name ?? '—') . ' (' . ($d->tenant->room->room_number ?? '') . ') - ' . $d->description,
                    'period_month'  => \Carbon\Carbon::parse($d->date)->month,
                    'period_year'   => \Carbon\Carbon::parse($d->date)->year,
                    'amount'        => $d->amount,
                    'notes'         => $d->notes,
                    'receipt_photo' => null,
                    'tenant_id'     => $d->tenant_id,
                ];
            });
        }

        // --- 2.5 Get Maintenance ---
        $maintenancesList = collect([]);
        if (!$selectedCategory || $selectedCategory === 'maintenance') {
            $maintenancesQuery = \App\Models\RoomMaintenance::with('room')->where('cost', '>', 0);
            if ($request->filled('month')) {
                $maintenancesQuery->where(function($q) use ($currentMonth) {
                    $q->whereMonth('done_date', $currentMonth)
                      ->orWhere(function($subQ) use ($currentMonth) {
                          $subQ->whereNull('done_date')->whereMonth('report_date', $currentMonth);
                      });
                });
            }
            if ($request->filled('year')) {
                $maintenancesQuery->where(function($q) use ($currentYear) {
                    $q->whereYear('done_date', $currentYear)
                      ->orWhere(function($subQ) use ($currentYear) {
                          $subQ->whereNull('done_date')->whereYear('report_date', $currentYear);
                      });
                });
            }

            $maintenancesList = $maintenancesQuery->get()->map(function($m) {
                $date = $m->done_date ? $m->done_date : $m->report_date;
                return (object) [
                    'id'            => $m->id,
                    'is_deposit'    => false,
                    'is_maintenance'=> true,
                    'expense_date'  => \Carbon\Carbon::parse($date)->startOfDay(),
                    'category'      => 'maintenance',
                    'category_label'=> 'Maintenance Kamar',
                    'category_icon' => 'bi-tools',
                    'title'         => '[Maintenance] ' . $m->item_name . ' — Kamar ' . ($m->room->room_number ?? 'N/A'),
                    'description'   => $m->notes,
                    'period_month'  => \Carbon\Carbon::parse($date)->month,
                    'period_year'   => \Carbon\Carbon::parse($date)->year,
                    'amount'        => $m->cost,
                    'notes'         => 'Vendor: ' . $m->vendor_name,
                    'receipt_photo' => null,
                ];
            });
        }

        // --- 3. Merge & Paginate ---
        $allExpenses = $expensesList->concat($depositsList)->concat($maintenancesList)->sortByDesc('expense_date')->values();
        
        $perPage = request('print') === 'all' ? 999999 : 15;
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $currentPageItems = $allExpenses->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $expenses = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems, $allExpenses->count(), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);

        $categories = ExpenseCategory::orderBy('name')->get();

        // --- 4. Ringkasan per kategori bulan ini ---
        $categoryTotals = Expense::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();
            
        $depositTotal = \App\Models\TenantDeposit::where('type', 'debit')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');
            
        if ($depositTotal > 0) {
            $categoryTotals->push((object)[
                'category' => 'deposit_deduction',
                'total' => $depositTotal
            ]);
        }
        
        $maintenanceTotal = $maintenancesList->sum('amount');
        if ($maintenanceTotal > 0) {
            $categoryTotals->push((object)[
                'category' => 'maintenance',
                'total' => $maintenanceTotal
            ]);
        }
        
        $categoryTotals = $categoryTotals->sortByDesc('total')->values();
        $totalThisMonth = $categoryTotals->sum('total');

        return view('expenses.index', compact(
            'expenses', 'categories', 'categoryTotals', 'totalThisMonth',
            'currentMonth', 'currentYear'
        ));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'      => 'required|exists:expense_categories,slug',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'amount'        => 'required|numeric|min:0',
            'expense_date'  => 'required|date',
            'period_month'  => 'required|integer|between:1,12',
            'period_year'   => 'required|integer|min:2020',
            'receipt_photo' => 'nullable|image|max:2048',
            'notes'         => 'nullable|string',
        ]);

        if ($request->hasFile('receipt_photo')) {
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('expense-receipts', 'public');
        }

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', "Pengeluaran berhasil dicatat!");
    }

    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category'      => 'required|exists:expense_categories,slug',
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'amount'        => 'required|numeric|min:0',
            'expense_date'  => 'required|date',
            'period_month'  => 'required|integer|between:1,12',
            'period_year'   => 'required|integer|min:2020',
            'receipt_photo' => 'nullable|image|max:2048',
            'notes'         => 'nullable|string',
        ]);

        if ($request->hasFile('receipt_photo')) {
            if ($expense->receipt_photo) Storage::disk('public')->delete($expense->receipt_photo);
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('expense-receipts', 'public');
        }

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', "Data pengeluaran berhasil diperbarui!");
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_photo) Storage::disk('public')->delete($expense->receipt_photo);
        $expense->delete();
        return redirect()->route('expenses.index')
            ->with('success', "Data pengeluaran berhasil dihapus.");
    }
}
