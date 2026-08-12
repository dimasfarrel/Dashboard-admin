<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;

class PayableController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');

        $query = Loan::where('type', 'payable');

        if ($status === 'paid') {
            $query->where('is_paid', true);
        } elseif ($status === 'unpaid') {
            $query->where('is_paid', false);
        }

        $loans = $query->orderBy('loan_date', 'desc')->paginate(15);

        // Active loans for the global repayment dropdown
        $activeLoans = Loan::where('type', 'payable')->where('is_paid', false)->orderBy('name')->get();

        // Unlinked repayments (Saldo bebas)
        $unlinkedRepayments = \App\Models\LoanRepayment::where('type', 'payable')->whereNull('loan_id')->orderBy('repayment_date', 'desc')->get();

        // Stats
        $totalLoansCount = Loan::where('type', 'payable')->count();
        $paidLoansCount = Loan::where('type', 'payable')->where('is_paid', true)->count();
        $unpaidLoansCount = Loan::where('type', 'payable')->where('is_paid', false)->count();

        $totalLoansAmount = Loan::where('type', 'payable')->sum('total_amount');
        $paidAmount = \App\Models\LoanRepayment::whereHas('loan', function($q) {
            $q->where('type', 'payable');
        })->orWhere(function($q) {
            $q->where('type', 'payable')->whereNull('loan_id');
        })->sum('amount');
        
        return view('payables.index', compact('loans', 'activeLoans', 'unlinkedRepayments', 'totalLoansCount', 'paidLoansCount', 'unpaidLoansCount', 'totalLoansAmount', 'paidAmount', 'status'));
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

        $validated['type'] = 'payable';
        Loan::create($validated);

        return redirect()->route('payables.index')->with('success', 'Data hutang berhasil ditambahkan!');
    }

    public function show(Loan $payable)
    {
        $payable->load('repayments');
        return view('payables.show', ['loan' => $payable]);
    }

    public function update(Request $request, Loan $payable)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'loan_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $payable->update($validated);
        $this->checkLoanStatus($payable);

        return redirect()->back()->with('success', 'Data hutang berhasil diperbarui!');
    }

    public function destroy(Loan $payable)
    {
        $payable->delete();
        return redirect()->route('payables.index')->with('success', 'Data hutang berhasil dihapus!');
    }

    // --- REPAYMENTS ---

    public function storeRepayment(Request $request, Loan $payable)
    {
        $validated = $request->validate([
            'repayment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $payable->repayments()->create($validated);
        $this->checkLoanStatus($payable);

        return redirect()->route('payables.show', $payable)->with('success', 'Data pembayaran hutang berhasil ditambahkan!');
    }

    public function storeGlobalRepayment(Request $request)
    {
        $validated = $request->validate([
            'loan_id' => 'nullable|exists:loans,id',
            'repayment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['type'] = 'payable';
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

    public function destroyRepayment(Loan $payable, \App\Models\LoanRepayment $repayment)
    {
        $repayment->delete();
        $this->checkLoanStatus($payable);

        return redirect()->route('payables.show', $payable)->with('success', 'Data pembayaran hutang berhasil dihapus!');
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
