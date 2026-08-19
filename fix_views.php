<?php
$files = [
    'resources/views/reports/total_omzet.blade.php' => ['var' => '$inc'],
    'resources/views/reports/total_pengeluaran.blade.php' => ['var' => '$exp'],
    'resources/views/reports/cash_flow.blade.php' => ['var' => '$trx']
];

foreach ($files as $file => $config) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    $var = $config['var'];

    // Fix the incorrect td in tbody and @empty
    $content = preg_replace(
        '/                    <td class="no-print"><\/td>\n                    <\/tr>\n                @empty\n                    <tr>\n                        <td colspan="4"([^>]+)>([^<]+)<\/td>\n                        <td class="no-print" style="text-align: center;">\n                            @if\(isset\('.preg_quote($var).'\[\'url\'\]\) && '.preg_quote($var).'\[\'url\'\] !== \'#\'\)\n                                <a href="\{\{ '.preg_quote($var).'\[\'url\'\] \}\}" class="btn btn-sm btn-info" style="padding: 2px 8px; font-size: 12px;"><i class="bi bi-eye"><\/i> Detail<\/a>\n                            @endif\n                        <\/td>\n                    <\/tr>/s',
        "                        <td class=\"no-print\" style=\"text-align: center;\">\n                            @if(isset({$var}['url']) && {$var}['url'] !== '#')\n                                <a href=\"{{ {$var}['url'] }}\" class=\"btn btn-sm btn-info\" style=\"padding: 2px 8px; font-size: 12px;\"><i class=\"bi bi-eye\"></i> Detail</a>\n                            @endif\n                        </td>\n                    </tr>\n                @empty\n                    <tr>\n                        <td colspan=\"5\"$1>$2</td>\n                    </tr>",
        $content
    );

    file_put_contents($file, $content);
    echo "Fixed $file\n";
}
