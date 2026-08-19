<?php
$file = 'app/Http/Controllers/ReportController.php';
$content = file_get_contents($file);

// Payment: change period_month to whereMonth('paid_at', ...)
$content = preg_replace(
    '/Payment::where\(\'status\', \'paid\'\)\s*->where\(\'period_month\', \$currentMonth\)\s*->where\(\'period_year\', \$currentYear\)/s',
    "Payment::where('status', 'paid')\n            ->whereMonth('paid_at', \$currentMonth)\n            ->whereYear('paid_at', \$currentYear)",
    $content
);

// OtherIncome: change period_month to whereMonth('income_date', ...)
$content = preg_replace(
    '/OtherIncome::where\(\'period_month\', \$currentMonth\)\s*->where\(\'period_year\', \$currentYear\)/s',
    "OtherIncome::whereMonth('income_date', \$currentMonth)\n            ->whereYear('income_date', \$currentYear)",
    $content
);

// Expense: change period_month to whereMonth('expense_date', ...)
$content = preg_replace(
    '/Expense::where\(\'period_month\', \$currentMonth\)\s*->where\(\'period_year\', \$currentYear\)/s',
    "Expense::whereMonth('expense_date', \$currentMonth)\n            ->whereYear('expense_date', \$currentYear)",
    $content
);

file_put_contents($file, $content);
echo "Cash basis applied to ReportController.php\n";
