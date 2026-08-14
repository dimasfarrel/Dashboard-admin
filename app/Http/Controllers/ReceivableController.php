<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $month = $request->input('month');
        $year = $request->input('year');
        $search = $request->input('search');

        $query = Loan::where('type', 'receivable');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($status === 'paid') {
            $query->where('is_paid', true);
        } elseif ($status === 'unpaid') {
            $query->where('is_paid', false);
        }

        if ($month) {
            $query->whereMonth('loan_date', $month);
        }
        if ($year) {
            $query->whereYear('loan_date', $year);
        }

        $loans = $query->orderBy('loan_date', 'desc')->paginate(15);

        // Active loans for the global repayment dropdown
        $activeLoans = Loan::where('type', 'receivable')->where('is_paid', false)->orderBy('name')->get();

        // Unlinked repayments (Saldo bebas)
        $unlinkedRepayments = \App\Models\LoanRepayment::where('type', 'receivable')->whereNull('loan_id')->orderBy('repayment_date', 'desc')->get();

        // Stats (Filtered)
        $statsQuery = clone $query;
        $totalLoansCount = (clone $statsQuery)->count();
        $paidLoansCount = (clone $statsQuery)->where('is_paid', true)->count();
        $unpaidLoansCount = (clone $statsQuery)->where('is_paid', false)->count();

        $totalLoansAmount = (clone $statsQuery)->sum('total_amount');
        
        $loanIds = (clone $statsQuery)->pluck('id');
        $repaymentsOfFilteredLoans = \App\Models\LoanRepayment::whereIn('loan_id', $loanIds)->sum('amount');
        
        $unlinkedQueryForStats = \App\Models\LoanRepayment::where('type', 'receivable')->whereNull('loan_id');
        if ($month) $unlinkedQueryForStats->whereMonth('repayment_date', $month);
        if ($year)  $unlinkedQueryForStats->whereYear('repayment_date', $year);
        
        $paidAmount = $repaymentsOfFilteredLoans + $unlinkedQueryForStats->sum('amount');
        

        // Get unique names for the filter dropdown
        $borrowerNames = Loan::where('type', 'receivable')->select('name')->distinct()->orderBy('name')->pluck('name');

        return view('receivables.index', compact(
            'loans', 'status', 'month', 'year', 'search',
            'totalLoansCount', 'paidLoansCount', 'unpaidLoansCount',
            'totalLoansAmount', 'paidAmount', 'activeLoans', 'unlinkedRepayments', 'borrowerNames'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'loan_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['type'] = 'receivable';
        Loan::create($validated);

        return redirect()->route('receivables.index')->with('success', 'Data piutang berhasil ditambahkan!');
    }

    public function show(Loan $receivable)
    {
        $receivable->load(['repayments' => function ($query) {
            $query->orderBy('repayment_date', 'desc')->orderBy('created_at', 'desc');
        }]);
        return view('receivables.show', ['loan' => $receivable]);
    }

    public function update(Request $request, Loan $receivable)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'loan_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $receivable->update($validated);
        $this->checkLoanStatus($receivable);

        return redirect()->back()->with('success', 'Data piutang berhasil diperbarui!');
    }

    public function destroy(Loan $receivable)
    {
        $receivable->delete();
        return redirect()->route('receivables.index')->with('success', 'Data piutang berhasil dihapus!');
    }

    // --- REPAYMENTS ---

    public function storeRepayment(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'repayment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['type'] = 'receivable';
        $loan->repayments()->create($validated);
        $this->checkLoanStatus($loan);

        return redirect()->route('receivables.show', $loan)->with('success', 'Data pelunasan berhasil ditambahkan!');
    }

    public function storeGlobalRepayment(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'nullable|exists:loans,id',
            'repayment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['type'] = 'receivable';
        $repayment = \App\Models\LoanRepayment::create($validated);

        if ($repayment->loan_id) {
            $loan = Loan::find($repayment->loan_id);
            $this->checkLoanStatus($loan);
            return redirect()->back()->with('success', 'Data pelunasan berhasil ditambahkan dan ditautkan ke pinjaman!');
        }

        return redirect()->back()->with('success', 'Data pelunasan bebas berhasil disimpan (belum ditautkan).');
    }

    public function linkGlobalRepayment(Request $request, \App\Models\LoanRepayment $repayment)
    {
        $validated = $request->validate([
            'loan_id' => 'required|exists:loans,id',
        ]);

        $repayment->update(['loan_id' => $validated['loan_id']]);
        $loan = Loan::find($validated['loan_id']);
        $this->checkLoanStatus($loan);

        return redirect()->back()->with('success', 'Data pelunasan berhasil ditautkan ke pinjaman!');
    }

    public function destroyGlobalRepayment(\App\Models\LoanRepayment $repayment)
    {
        $loan = $repayment->loan;
        $repayment->delete();
        
        if ($loan) {
            $this->checkLoanStatus($loan);
        }

        return redirect()->back()->with('success', 'Data pelunasan berhasil dihapus!');
    }

    public function destroyRepayment(Loan $loan, \App\Models\LoanRepayment $repayment)
    {
        $repayment->delete();
        $this->checkLoanStatus($loan);

        return redirect()->route('receivables.show', $loan)->with('success', 'Data pelunasan berhasil dihapus!');
    }

    private function checkLoanStatus(Loan $loan)
    {
        $paid = $loan->repayments()->sum('amount');
        $isPaid = $paid >= $loan->total_amount;
        
        if ($loan->is_paid !== $isPaid) {
            $loan->update(['is_paid' => $isPaid]);
        }
    }
}
