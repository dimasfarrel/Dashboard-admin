<?php
$files = [
    'resources/views/dashboard.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/reports/total_omzet.blade.php',
    'resources/views/reports/total_pengeluaran.blade.php',
    'resources/views/reports/cash_flow.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Omzet
    $content = str_replace('Laporan Omzet Total', 'Laporan Omzet Periode', $content);
    $content = str_replace('Omzet Total', 'Omzet Periode', $content);
    $content = str_replace('TOTAL OMZET', 'TOTAL OMZET PERIODE', $content);

    // Pengeluaran
    $content = str_replace('Laporan Pengeluaran Total', 'Laporan Pengeluaran Periode', $content);
    $content = str_replace('Pengeluaran Total', 'Pengeluaran Periode', $content);
    $content = str_replace('TOTAL PENGELUARAN', 'TOTAL PENGELUARAN PERIODE', $content);

    file_put_contents($file, $content);
    echo "Renamed in $file\n";
}
