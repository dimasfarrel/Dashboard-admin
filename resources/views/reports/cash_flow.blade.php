@extends('layouts.app')
@section('title', 'Laporan Arus Kas')

@section('page-title', 'Laporan Arus Kas')
@section('page-subtitle', 'Rekap Seluruh Pergerakan Uang Masuk & Keluar')

@push('styles')
<style>
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}
.summary-card {
    background: var(--bg-card, #1e293b);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    border: 1px solid var(--border-color, #334155);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.summary-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 16px;
}
.icon-masuk { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.icon-keluar { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.icon-saldo { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.summary-content { flex: 1; }
.summary-label {
    font-size: 13px;
    color: var(--text-secondary, #94a3b8);
    font-weight: 500;
    margin-bottom: 4px;
}
.summary-value {
    font-size: 22px;
    font-weight: 700;
    color: var(--text-primary, #f8fafc);
}
.table th, .table td {
    white-space: nowrap;
}
</style>
@endpush

@section('topbar-actions')
    <button onclick="window.print()" class="btn btn-info"><i class="bi bi-printer"></i> Cetak Laporan</button>
@endsection

@section('content')

{{-- Filter Card --}}
<div class="card filter-card no-print" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.cash_flow') }}" class="flex items-center gap-3 flex-wrap">
            <div style="display:flex; flex-direction:column; gap:4px;">
                <label for="month" style="font-size:12px; font-weight:600; color:var(--text-secondary);">Bulan</label>
                <select name="month" id="month" class="form-control" style="width:150px;">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; flex-direction:column; gap:4px;">
                <label for="year" style="font-size:12px; font-weight:600; color:var(--text-secondary);">Tahun</label>
                <input type="number" name="year" id="year" class="form-control" style="width:100px;" value="{{ $currentYear }}" min="2020">
            </div>
            <div style="display:flex; flex-direction:column; gap:4px; margin-top:20px;">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

{{-- Print Header --}}
<div class="print-header" style="display: none; margin-bottom: 25px; border-bottom: 2px solid #1e293b; padding-bottom: 15px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div style="text-align: left;">
            <h1 style="margin: 0; font-size: 26px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 1px;">LAPORAN ARUS KAS</h1>
            <h2 style="margin: 4px 0 0 0; font-size: 16px; font-weight: 600; color: #334155;">KOST MALANG</h2>
        </div>
        <div style="text-align: right;">
            <p style="margin: 0; font-size: 14px; font-weight: 600; color: #0f172a;">
                Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
            </p>
            <p style="margin: 4px 0 0 0; font-size: 11px; color: #64748b;">Dicetak pada: {{ now()->translatedFormat('d M Y, H:i') }}</p>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="summary-cards no-print">
    <div class="summary-card">
        <div class="summary-icon icon-masuk">
            <i class="bi bi-box-arrow-in-down-left"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Total Kas Masuk</div>
            <div class="summary-value">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon icon-keluar">
            <i class="bi bi-box-arrow-up-right"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Total Kas Keluar</div>
            <div class="summary-value">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon icon-saldo">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Saldo Kas Bersih</div>
            <div class="summary-value" style="color: {{ $saldoBersih >= 0 ? '#10b981' : '#ef4444' }};">
                {{ $saldoBersih >= 0 ? '' : '-' }}Rp {{ number_format(abs($saldoBersih), 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

{{-- Tabel Arus Kas --}}
<div class="card" style="margin-bottom: 30px; border:1px solid #3b82f6;">
    <div class="card-header" style="background-color: rgba(59, 130, 246, 0.1); border-bottom: 1px solid #3b82f6;">
        <h2 class="card-title" style="color: #1d4ed8; font-weight: 700;"><i class="bi bi-arrow-left-right"></i> Arus Kas Bulan {{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->translatedFormat('F Y') }}</h2>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr style="background-color: #f8fafc;">
                    <th class="col-date">Tanggal</th>
                    <th class="col-category">Kategori</th>
                    <th class="col-desc">Deskripsi</th>
                    <th class="col-in" style="text-align: right; background-color: #ecfdf5;">Kas Masuk</th>
                    <th class="col-out" style="text-align: right; background-color: #fef2f2;">Kas Keluar</th>
                    <th class="no-print col-action" style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                    <tr>
                        <td style="color: #475569;">{{ \Carbon\Carbon::parse($trx['date'])->translatedFormat('d-M-Y') }}</td>
                        <td><strong>{{ $trx['category'] }}</strong></td>
                        <td>{{ $trx['description'] }}</td>
                        <td style="text-align: right; color: #047857; font-weight: {{ $trx['kas_masuk'] > 0 ? '600' : '400' }};">
                            {{ $trx['kas_masuk'] > 0 ? 'Rp ' . number_format($trx['kas_masuk'], 0, ',', '.') : '-' }}
                        </td>
                        <td style="text-align: right; color: #b91c1c; font-weight: {{ $trx['kas_keluar'] > 0 ? '600' : '400' }};">
                            {{ $trx['kas_keluar'] > 0 ? 'Rp ' . number_format($trx['kas_keluar'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="no-print" style="text-align: center;">
                            @if(isset($trx['url']) && $trx['url'] !== '#')
                                <a href="{{ $trx['url'] }}" class="btn btn-info btn-sm btn-icon" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">Belum ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row" style="font-weight: bold; background-color: #f0f9ff;">
                    <td colspan="3" style="text-align: right; padding-right: 20px;">TOTAL:</td>
                    <td style="text-align: right; color: #047857;">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #b91c1c;">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</td>
                    <td class="no-print"></td>
                </tr>
                <tr class="saldo-row" style="font-weight: bold; background-color: #eff6ff;">
                    <td colspan="3" style="text-align: right; font-size: 14px; padding-right: 20px;">SALDO KAS BERSIH:</td>
                    <td colspan="2" style="text-align: right; font-size: 16px; color: {{ $saldoBersih >= 0 ? '#047857' : '#b91c1c' }};">
                        {{ $saldoBersih >= 0 ? '' : '-' }}Rp {{ number_format(abs($saldoBersih), 0, ',', '.') }}
                    </td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Print Footer (Fallback for browsers that don't support @page counters) --}}
<div class="print-footer-fallback" style="display: none; position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #64748b; padding-top: 5px; border-top: 1px solid #cbd5e1;">
    Dicetak dari Sistem Admin Kost
</div>

{{-- CSS KHUSUS PRINT --}}
<style>
@media print {
    /* Hide non-printable elements */
    .sidebar, .topbar, .filter-card, .no-print, .summary-cards {
        display: none !important;
    }
    
    /* Reset body for clean slate */
    body {
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .main-content, .page-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
    
    .card {
        border: none !important;
        margin-bottom: 0 !important;
        box-shadow: none !important;
    }
    .card-header, .print-footer-fallback {
        display: none !important;
    }

    /* Print Header - Professional Centered */
    .print-header {
        display: block !important;
        text-align: center !important;
        margin-bottom: 25px !important;
        border-bottom: none !important;
        padding-bottom: 0 !important;
    }
    .print-header h1 {
        font-family: "Times New Roman", Times, serif !important;
        font-size: 20pt !important;
        font-weight: bold !important;
        color: #000 !important;
        margin: 0 0 5px 0 !important;
        text-transform: uppercase !important;
        letter-spacing: 2px !important;
    }
    .print-header h2 {
        font-family: "Times New Roman", Times, serif !important;
        font-size: 14pt !important;
        font-weight: bold !important;
        color: #000 !important;
        margin: 0 0 10px 0 !important;
    }
    .print-header p {
        font-family: "Times New Roman", Times, serif !important;
        font-size: 11pt !important;
        color: #000 !important;
        margin: 2px 0 !important;
    }

    /* Table - Professional Accounting Standard */
    .table-wrapper {
        overflow: visible !important;
    }
    table {
        width: 100% !important;
        table-layout: auto !important; 
        border-collapse: collapse !important;
        font-family: "Times New Roman", Times, serif !important;
    }
    
    th, td {
        border: none !important; /* No vertical borders in pro financial reports */
        padding: 8px 10px !important;
        font-size: 11pt !important;
        color: #000 !important;
        vertical-align: top !important;
    }
    
    /* Repeat header on every page */
    thead {
        display: table-header-group !important;
    }
    
    thead th {
        border-top: 2px solid #000 !important;
        border-bottom: 1px solid #000 !important;
        font-weight: bold !important;
        background-color: transparent !important;
        text-transform: uppercase !important;
        font-size: 10pt !important;
    }

    /* Subtle row separators */
    tbody tr td {
        border-bottom: 1px dotted #ccc !important;
    }
    
    /* Ensure totals don't repeat */
    tfoot {
        display: table-row-group !important;
    }
    
    tfoot tr td {
        background-color: transparent !important;
        color: #000 !important;
    }
    
    /* Accounting totals styling */
    tfoot tr.total-row td {
        border-top: 1px solid #000 !important;
        border-bottom: none !important;
        font-weight: bold !important;
    }
    
    tfoot tr.saldo-row td {
        border-top: 1px solid #000 !important;
        border-bottom: 4px double #000 !important; /* Standard double underline */
        font-weight: bold !important;
        font-size: 12pt !important;
    }

    /* Page Configuration */
    @page {
        size: landscape;
        margin: 15mm 20mm;
        
        /* Attempt CSS3 page numbering (Supported in modern engines) */
        @bottom-right {
            content: "Halaman " counter(page) " dari " counter(pages);
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            color: #000;
        }
    }
}
</style>

@endsection
