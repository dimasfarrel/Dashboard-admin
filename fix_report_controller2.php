<?php
$file = 'app/Http/Controllers/ReportController.php';
$content = file_get_contents($file);

// Fix Sewa Kost
$content = preg_replace(
    '/\'url\' => \'#\', \/\/ No direct show page for payment yet/',
    "'url' => route('payments.show', \$item->id),",
    $content
);

// Fix Pengeluaran Kost
$content = preg_replace(
    '/\'url\' => \'#\', \/\/ No direct show page for expenses/',
    "'url' => route('expenses.show', \$item->id),",
    $content
);

file_put_contents($file, $content);
echo "ReportController URLs restored.\n";
