<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantDeposit;
use Illuminate\Http\Request;

class TenantDepositController extends Controller
{
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

        return redirect()->route('tenants.show', $tenant)
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

        return redirect()->route('tenants.show', $tenant)
            ->with('success', "Pengurangan deposit Rp " . number_format($validated['amount'], 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Hapus transaksi deposit.
     */
    public function destroy(TenantDeposit $deposit)
    {
        $tenant = $deposit->tenant;
        $deposit->delete();

        return redirect()->route('tenants.show', $tenant)
            ->with('success', "Transaksi deposit berhasil dihapus.");
    }
}
