<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - Kost Malang</title>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
            color: #111827;
            background: #fff;
            margin: 0;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #4b5563;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }
        .table th, .table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }
        .table th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        .money {
            text-align: right;
            white-space: nowrap;
        }
        .summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .summary-box {
            width: 300px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .summary-row.total {
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-weight: 600;
            font-size: 16px;
        }
        .text-green { color: #00d4aa; }
        .text-red { color: #f43f5e; }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            border: 1px dashed #e5e7eb;
            border-radius: 8px;
        }

        .btn-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        @media print {
            body { padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1>Laporan Keuangan Kost Malang</h1>
        <p>Periode: {{ \Carbon\Carbon::now()->setMonth((int)$month)->translatedFormat('F') }} {{ $year }}</p>
    </div>

    @if($transactions->isEmpty())
        <div class="no-data">
            Tidak ada data transaksi pada periode dan kategori yang dipilih.
        </div>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th style="width:100px;">Tanggal</th>
                    <th style="width:150px;">Kategori</th>
                    <th>Keterangan</th>
                    <th style="width:130px; text-align:right;">Pemasukan (Rp)</th>
                    <th style="width:130px; text-align:right;">Pengeluaran (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $trx)
                    <tr>
                        <td>{{ $trx['date']->translatedFormat('d-M-Y') }}</td>
                        <td>{{ $trx['category'] }}</td>
                        <td>
                            {{ $trx['description'] }}
                            @if($trx['notes'])
                                <br><small style="color:#6b7280;">Catatan: {{ $trx['notes'] }}</small>
                            @endif
                        </td>
                        <td class="money">
                            @if($trx['type'] == 'income')
                                <span class="text-green">{{ number_format($trx['amount'], 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="money">
                            @if($trx['type'] == 'expense')
                                <span class="text-red">{{ number_format($trx['amount'], 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Total Pemasukan</span>
                    <span class="text-green">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span>Total Pengeluaran</span>
                    <span class="text-red">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row total">
                    <span>Saldo Akhir</span>
                    <span class="{{ $netIncome >= 0 ? 'text-green' : 'text-red' }}">
                        Rp {{ number_format($netIncome, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    <button class="btn-print" onclick="window.print()">Cetak Laporan</button>

</body>
</html>
