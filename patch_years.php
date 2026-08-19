<?php
$files = [
    'resources/views/other-incomes/index.blade.php',
    'resources/views/expenses/index.blade.php',
    'resources/views/maintenances/index.blade.php',
    'resources/views/receivables/index.blade.php',
    'resources/views/payables/index.blade.php',
    'resources/views/lodgings/index.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace(
            "value=\"{{ request('year') }}\"",
            "value=\"{{ request('year', date('Y')) }}\"",
            $content
        );
        file_put_contents($file, $content);
        echo "Patched: $file\n";
    }
}
