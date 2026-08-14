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
        $query = Expense::query();

        if ($request->filled('month'))    $query->where('period_month', $request->month);
        if ($request->filled('year'))     $query->where('period_year', $request->year);
        if ($request->filled('category')) $query->where('category', $request->category);

        $expenses = $query->orderByDesc('expense_date')->paginate(15)->appends(request()->query());
        $categories = ExpenseCategory::orderBy('name')->get();

        $currentMonth = $request->month ?? now()->month;
        $currentYear  = $request->year  ?? now()->year;

        // Ringkasan per kategori bulan ini
        $categoryTotals = Expense::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

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
