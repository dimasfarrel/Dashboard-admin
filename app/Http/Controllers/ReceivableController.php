<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;

class ReceivableController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $query = Loan::where('type', 'receivable');

        if ($status === 'paid') {
            $query->where('is_paid', true);
        } elseif ($status === 'unpaid') {
            $query->where('is_paid', false);
        }

        $loans = $query->orderBy('loan_date', 'desc')->paginate(15);

        // Active loans for the global repayment dropdown
        $activeLoans = Loan::where('type', 'receivable')->where('is_paid', false)->orderBy('name')->get();

        // Unlinked repayments (Saldo bebas)
        $unlinkedRepayments = \App\Models\LoanRepayment::where('type', 'receivable')->whereNull('loan_id')->orderBy('repayment_date', 'desc')->get();

        // Stats
        $totalLoansCount = Loan::where('type', 'receivable')->count();
        $paidLoansCount = Loan::where('type', 'receivable')->where('is_paid', true)->count();
        $unpaidLoansCount = Loan::where('type', 'receivable')->where('is_paid', false)->count();

        $totalLoansAmount = Loan::where('type', 'receivable')->sum('total_amount');
        $paidAmount = \App\Models\LoanRepayment::whereHas('loan', function($q) {
            $q->where('type', 'receivable');
        })->orWhere(function($q) {
            $q->where('type', 'receivable')->whereNull('loan_id');
        })->sum('amount');
        
        return view('receivables.index', compact('loans', 'activeLoans', 'unlinkedRepayments', 'totalLoansCount', 'paidLoansCount', 'unpaidLoansCount', 'totalLoansAmount', 'paidAmount', 'status'));
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

    public function show(Loan $loan)
    {
        $loan->load('repayments');
        return view('receivables.show', compact('loan'));
    }

    public function update(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'loan_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $loan->update($validated);
        $this->checkLoanStatus($loan);

        return redirect()->back()->with('success', 'Data piutang berhasil diperbarui!');
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
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
