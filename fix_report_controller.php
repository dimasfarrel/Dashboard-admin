<?php
$file = 'app/Http/Controllers/ReportController.php';
$content = file_get_contents($file);

// Fix Sewa Kost
$content = preg_replace(
    '/\'url\' => \$item->loan_id \? route\(\'receivables\.show\', \$item->loan_id\) : \'#\',\s*\'url\' => route\(\'lodgings\.show\', \$item->id\),\s*\'url\' => route\(\'payments\.show\', \$item->id\),\s*\'url\' => route\(\'payments\.show\', \$item->id\),/',
    "'url' => '#', // No direct show page for payment yet",
    $content
);

// Fix Pengeluaran Kost
$content = preg_replace(
    '/\'url\' => \$item->tenant_id \? route\(\'tenants\.show\', \$item->tenant_id\) : \'#\',\s*\'url\' => route\(\'expenses\.show\', \$item->id\),\s*\'url\' => route\(\'expenses\.show\', \$item->id\),/',
    "'url' => '#', // No direct show page for expenses",
    $content
);

file_put_contents($file, $content);
echo "ReportController patched.\n";
