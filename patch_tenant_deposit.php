<?php
$file = 'app/Http/Controllers/TenantDepositController.php';
$content = file_get_contents($file);

// Add index and storeGlobal
$newMethods = <<<PHP
    /**
     * Laporan Deposit
     */
    public function index(Request \$request)
    {
        \$deposits = TenantDeposit::with('tenant')->orderBy('date', 'desc')->paginate(20);
        \$tenants = Tenant::whereIn('status', ['active', 'inactive'])->get(); // Active and inactive might have deposits
        return view('tenant-deposits.index', compact('deposits', 'tenants'));
    }

    /**
     * Simpan deposit secara global (dari laporan deposit)
     */
    public function storeGlobal(Request \$request)
    {
        if (\$request->has('amount')) {
            \$request->merge(['amount' => str_replace('.', '', \$request->amount)]);
        }

        \$validated = \$request->validate([
            'tenant_id'   => 'required|exists:tenants,id',
            'type'        => 'required|in:credit,debit',
            'amount'      => 'required|integer|min:1',
            'description' => 'required|string|max:255',
            'date'        => 'required|date',
            'notes'       => 'nullable|string',
        ]);

        if (\$validated['type'] === 'debit') {
            \$tenant = Tenant::findOrFail(\$validated['tenant_id']);
            if (\$validated['amount'] > \$tenant->deposit_balance) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Jumlah pengurangan melebihi saldo deposit.");
            }
        }

        TenantDeposit::create(\$validated);

        return redirect()->route('tenant-deposits.index')
            ->with('success', "Data deposit berhasil dicatat.");
    }
PHP;

$content = preg_replace('/(class TenantDepositController extends Controller\s*\{)/s', "$1\n\n$newMethods", $content);

// Fix redirects in update and destroy
$content = str_replace(
    "return redirect()->route('tenants.show', \$deposit->tenant_id)\n            ->with('success'",
    "return redirect()->back()\n            ->with('success'",
    $content
);

$content = str_replace(
    "return redirect()->route('tenants.show', \$tenant)\n            ->with('success'",
    "return redirect()->back()\n            ->with('success'",
    $content
);

file_put_contents($file, $content);
echo "TenantDepositController patched.\n";
