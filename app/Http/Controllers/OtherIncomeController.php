<?php

namespace App\Http\Controllers;

use App\Models\OtherIncome;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OtherIncomeController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = $request->input('month', now()->month);
        $currentYear  = $request->input('year', now()->year);

        $query = OtherIncome::query();

        if ($request->filled('month')) $query->where('period_month', $currentMonth);
        if ($request->filled('year'))  $query->where('period_year', $currentYear);
        if ($request->filled('category')) $query->where('category', $request->category);

        $perPage = request('print') === 'all' ? 999999 : 15;
        $incomes = $query->orderByDesc('income_date')->paginate($perPage)->appends(request()->query());

        // Category totals for this month
        $categoryTotals = OtherIncome::where('period_month', $currentMonth)
            ->where('period_year', $currentYear)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $totalThisMonth = $categoryTotals->sum('total');

        return view('other-incomes.index', compact(
            'incomes', 'categoryTotals', 'totalThisMonth',
            'currentMonth', 'currentYear'
        ));
    }

    public function create()
    {
        return view('other-incomes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'amount'       => 'required|numeric|min:0',
            'income_date'  => 'required|date',
            'period_month' => 'required|integer|between:1,12',
            'period_year'  => 'required|integer|min:2020',
            'receipt_photo'=> 'nullable|image|max:2048',
            'notes'        => 'nullable|string',
        ]);

        if ($request->hasFile('receipt_photo')) {
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('other-incomes', 'public');
        }

        OtherIncome::create($validated);

        return redirect()->route('other-incomes.index')
            ->with('success', 'Pendapatan lain-lain berhasil dicatat!');
    }

    public function show(OtherIncome $otherIncome)
    {
        return view('other-incomes.show', compact('otherIncome'));
    }

    public function edit(OtherIncome $otherIncome)
    {
        return view('other-incomes.edit', compact('otherIncome'));
    }

    public function update(Request $request, OtherIncome $otherIncome)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'category'     => 'required|string|max:100',
            'amount'       => 'required|numeric|min:0',
            'income_date'  => 'required|date',
            'period_month' => 'required|integer|between:1,12',
            'period_year'  => 'required|integer|min:2020',
            'receipt_photo'=> 'nullable|image|max:2048',
            'notes'        => 'nullable|string',
        ]);

        if ($request->hasFile('receipt_photo')) {
            if ($otherIncome->receipt_photo) Storage::disk('public')->delete($otherIncome->receipt_photo);
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('other-incomes', 'public');
        }

        $otherIncome->update($validated);

        return redirect()->route('other-incomes.index')
            ->with('success', 'Data pendapatan berhasil diperbarui!');
    }

    public function destroy(OtherIncome $otherIncome)
    {
        if ($otherIncome->receipt_photo) Storage::disk('public')->delete($otherIncome->receipt_photo);
        $otherIncome->delete();
        return redirect()->route('other-incomes.index')
            ->with('success', 'Data pendapatan berhasil dihapus.');
    }
}
