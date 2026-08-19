<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantDeposit;
use Illuminate\Http\Request;

class TenantDepositController extends Controller
{

    /**
     * Laporan Deposit
     */
    public function index(Request $request)
    {
        $deposits = TenantDeposit::with('tenant')->orderBy('date', 'desc')->paginate(20);
        $tenants = Tenant::whereIn('status', ['active', 'inactive'])->get(); // Active and inactive might have deposits
        return view('tenant-deposits.index', compact('deposits', 'tenants'));
    }

    /**
     * Simpan deposit secara global (dari laporan deposit)
     */
    public function storeGlobal(Request $request)
    {
        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        }

        $validated = $request->validate([
            'tenant_id'   => 'required|exists:tenants,id',
            'type'        => 'required|in:credit,debit',
            'amount'      => 'required|integer|min:1',
            'description' => 'required|string|max:255',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        if ($validated['type'] === 'debit') {
            $tenant = Tenant::findOrFail($validated['tenant_id']);
            if ($validated['amount'] > $tenant->deposit_balance) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Jumlah pengurangan melebihi saldo deposit.");
            }
        }

        TenantDeposit::create($validated);

        return redirect()->route('tenant-deposits.index')
            ->with('success', "Data deposit berhasil dicatat.");
    }
    /**
     * Simpan deposit masuk (credit) dari penyewa.
     */
    public function store(Request $request, Tenant $tenant)
    {
        // Hilangkan format titik sebelum divalidasi
        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        }

        $validated = $request->validate([
            'amount'      => 'required|integer|min:1',
            'description' => 'required|string|max:255',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['type']      = 'credit';

        TenantDeposit::create($validated);

        return redirect()->back()
            ->with('success', "Deposit Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Simpan pengurangan deposit (debit) — penggantian barang rusak dll.
     */
    public function storeDeduction(Request $request, Tenant $tenant)
    {
        // Hilangkan format titik sebelum divalidasi
        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        }

        $validated = $request->validate([
            'amount'      => 'required|integer|min:1',
            'description' => 'required|string|max:255',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        // Load deposits untuk cek saldo
        $tenant->load('deposits');
        $balance = $tenant->deposit_balance;

        if ($validated['amount'] > $balance) {
            return redirect()->back()
                ->withInput()
                ->with('deduct_error', "Jumlah pengurangan (Rp " . number_format($validated['amount'], 0, ',', '.') . ") melebihi saldo deposit (Rp " . number_format($balance, 0, ',', '.') . ").");
        }

        $validated['tenant_id'] = $tenant->id;
        $validated['type']      = 'debit';

        TenantDeposit::create($validated);

        return redirect()->back()
            ->with('success', "Pengurangan deposit Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Update transaksi deposit.
     */
    public function update(Request $request, TenantDeposit $deposit)
    {
        if ($request->has('amount')) {
            $request->merge(['amount' => str_replace('.', '', $request->amount)]);
        }

        $validated = $request->validate([
            'amount'      => 'required|integer|min:1',
            'description' => 'required|string|max:255',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        $deposit->update($validated);

        return redirect()->back()
            ->with('success', "Transaksi deposit berhasil diupdate.");
    }

    /**
     * Hapus transaksi deposit.
     */
    public function destroy(TenantDeposit $deposit)
    {
        $tenant = $deposit->tenant;
        $deposit->delete();

        return redirect()->back()
            ->with('success', "Transaksi deposit berhasil dihapus.");
    }
}
