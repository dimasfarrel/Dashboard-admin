<?php
$content = file_get_contents('app/Http/Controllers/ReportController.php');

$replacements = [
    ["'amount' => (float) \$item->amount,", "'amount' => (float) \$item->amount,\n                    'url' => route('payments.show', \$item->id),", "'category' => 'Sewa Kost',"],
    ["'amount' => (float) \$item->calculateTotal(),", "'amount' => (float) \$item->calculateTotal(),\n                    'url' => route('lodgings.show', \$item->id),", "'category' => 'Penginapan',"],
    ["'amount' => (float) \$item->amount,", "'amount' => (float) \$item->amount,\n                    'url' => route('other-incomes.show', \$item->id),", "'category' => 'Lain-lain',"],
    ["'amount' => (float) \$item->amount,", "'amount' => (float) \$item->amount,\n                    'url' => \$item->loan_id ? route('receivables.show', \$item->loan_id) : '#',", "'category' => 'Pelunasan Piutang',"],
    ["'amount' => (float) \$item->amount,", "'amount' => (float) \$item->amount,\n                    'url' => route('expenses.show', \$item->id),", "'category' => 'Pengeluaran',"],
    ["'amount' => (float) \$item->amount,", "'amount' => (float) \$item->amount,\n                    'url' => \$item->tenant_id ? route('tenants.show', \$item->tenant_id) : '#',", "'category' => 'Pengembalian Deposit',"],

    ["'kas_keluar' => 0,", "'kas_keluar' => 0,\n                    'url' => route('payments.show', \$item->id),", "'category' => 'Sewa Kost',"],
    ["'kas_keluar' => 0,", "'kas_keluar' => 0,\n                    'url' => route('lodgings.show', \$item->id),", "'category' => 'Penginapan',"],
    ["'kas_keluar' => 0,", "'kas_keluar' => 0,\n                    'url' => route('other-incomes.show', \$item->id),", "'category' => 'Pendapatan Lain',"],
    ["'kas_keluar' => 0,", "'kas_keluar' => 0,\n                    'url' => \$item->loan_id ? route('receivables.show', \$item->loan_id) : '#',", "'category' => 'Pelunasan Piutang',"],
    ["'kas_keluar' => 0,", "'kas_keluar' => 0,\n                    'url' => route('payables.show', \$item->id),", "'category' => 'Hutang Masuk',"],
    ["'kas_keluar' => 0,", "'kas_keluar' => 0,\n                    'url' => \$item->tenant_id ? route('tenants.show', \$item->tenant_id) : '#',", "'category' => 'Deposit Masuk',"],

    ["'kas_keluar' => (float) \$item->amount,", "'kas_keluar' => (float) \$item->amount,\n                    'url' => route('expenses.show', \$item->id),", "'category' => 'Pengeluaran',"],
    ["'kas_keluar' => (float) \$item->total_amount,", "'kas_keluar' => (float) \$item->total_amount,\n                    'url' => route('receivables.show', \$item->id),", "'category' => 'Piutang Keluar',"],
    ["'kas_keluar' => (float) \$item->amount,", "'kas_keluar' => (float) \$item->amount,\n                    'url' => \$item->loan_id ? route('payables.show', \$item->loan_id) : '#',", "'category' => 'Pelunasan Hutang',"],
    ["'kas_keluar' => (float) \$item->amount,", "'kas_keluar' => (float) \$item->amount,\n                    'url' => \$item->tenant_id ? route('tenants.show', \$item->tenant_id) : '#',", "'category' => 'Pengembalian Deposit',"],
];

$lines = explode("\n", $content);
foreach ($replacements as $rep) {
    $search = $rep[0];
    $replace = $rep[1];
    $context = $rep[2];
    
    // Find context line, then replace next occurrence of search
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], $context) !== false) {
            for ($j = $i; $j < count($lines); $j++) {
                if (strpos($lines[$j], $search) !== false) {
                    $lines[$j] = str_replace($search, $replace, $lines[$j]);
                    break;
                }
            }
        }
    }
}

file_put_contents('app/Http/Controllers/ReportController.php', implode("\n", $lines));
echo "ReportController patched.\n";
