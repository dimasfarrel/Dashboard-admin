<?php
function patchFile($file, $type) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        return;
    }
    $content = file_get_contents($file);
    
    // 1. thead
    if (strpos($content, '<th class="no-print" style="width: 80px; text-align: center;">Aksi</th>') === false) {
        $content = preg_replace(
            '/(<th style="text-align: right;(?:[^>]+)">.*?<\/th>)/s',
            "$1\n                    <th class=\"no-print\" style=\"width: 80px; text-align: center;\">Aksi</th>",
            $content
        );
    }

    // 2. tbody
    $aksiCol = "                        <td class=\"no-print\" style=\"text-align: center;\">\n                            @if(isset(\$inc['url']) && \$inc['url'] !== '#')\n                                <a href=\"{{ \$inc['url'] }}\" class=\"btn btn-sm btn-info\" style=\"padding: 2px 8px; font-size: 12px;\"><i class=\"bi bi-eye\"></i> Detail</a>\n                            @endif\n                        </td>\n";
    if ($type == 'omzet' || $type == 'pengeluaran') {
        $varName = $type == 'omzet' ? '$inc' : '$exp';
        $itemVar = $type == 'omzet' ? 'inc' : 'exp';
        
        $aksiCol = "                        <td class=\"no-print\" style=\"text-align: center;\">\n                            @if(isset(\${$itemVar}['url']) && \${$itemVar}['url'] !== '#')\n                                <a href=\"{{ \${$itemVar}['url'] }}\" class=\"btn btn-sm btn-info\" style=\"padding: 2px 8px; font-size: 12px;\"><i class=\"bi bi-eye\"></i> Detail</a>\n                            @endif\n                        </td>";
        
        $content = preg_replace(
            '/(<td style="text-align: right;[^>]+>Rp {{ number_format\(\$[^>]+>.*?<\/td>)/s',
            "$1\n$aksiCol",
            $content
        );
    } else if ($type == 'cashflow') {
        $aksiCol = "                        <td class=\"no-print\" style=\"text-align: center;\">\n                            @if(isset(\$trx['url']) && \$trx['url'] !== '#')\n                                <a href=\"{{ \$trx['url'] }}\" class=\"btn btn-sm btn-info\" style=\"padding: 2px 8px; font-size: 12px;\"><i class=\"bi bi-eye\"></i> Detail</a>\n                            @endif\n                        </td>";
        $content = preg_replace(
            '/(<td style="text-align: right;[^>]+>Rp {{ number_format\(\$trx\[\'kas_keluar\'\][^>]+>.*?<\/td>)/s',
            "$1\n$aksiCol",
            $content
        );
    }

    // 3. tfoot
    if (strpos($content, '<td class="no-print"></td>') === false) {
        $content = preg_replace(
            '/(<td style="text-align: right;[^>]+>Rp {{ number_format.*?<\/td>)/s',
            "$1\n                    <td class=\"no-print\"></td>",
            $content
        );
    }

    // 4. print css
    if (strpos($content, 'body, .main-content, .page-content, span, strong, td, th {') === false) {
        $content = str_replace(
            'body, .main-content, .page-content {',
            "body, .main-content, .page-content, span, strong, td, th, p, h1, h2, h3, h4, h5, h6, a, div {\n        color: #000 !important;\n    }\n    body, .main-content, .page-content {",
            $content
        );
    }
    
    file_put_contents($file, $content);
    echo "Patched $file\n";
}

patchFile('resources/views/reports/total_omzet.blade.php', 'omzet');
patchFile('resources/views/reports/total_pengeluaran.blade.php', 'pengeluaran');
patchFile('resources/views/reports/cash_flow.blade.php', 'cashflow');
