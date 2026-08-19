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
<div class="print-header" style="display: none; text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px;">
    <h1 style="margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase;">Laporan Arus Kas Kost Malang</h1>
    <p style="margin: 5px 0 0 0; font-size: 14px;">
        Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
    </p>
    <p style="margin: 2px 0 0 0; font-size: 12px; color: #555;">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
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
                    <th style="width: 100px;">Tanggal</th>
                    <th style="width: 160px;">Kategori</th>
                    <th>Deskripsi</th>
                    <th style="text-align: right; width: 150px; background-color: #ecfdf5;">Kas Masuk</th>
                    <th class="no-print" style="width: 80px; text-align: center;">Aksi</th>
                    <th style="text-align: right; width: 150px; background-color: #fef2f2;">Kas Keluar</th>
                    <th class="no-print" style="width: 80px; text-align: center;">Aksi</th>
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">Belum ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f0f9ff;">
                    <td colspan="3" style="text-align: right;">TOTAL:</td>
                    <td style="text-align: right; color: #047857;">Rp {{ number_format($totalKasMasuk, 0, ',', '.') }}</td>
                    <td class="no-print"></td>
                    <td style="text-align: right; color: #b91c1c;">Rp {{ number_format($totalKasKeluar, 0, ',', '.') }}</td>
                    <td class="no-print"></td>
                </tr>
                <tr style="font-weight: bold; background-color: #eff6ff;">
                    <td colspan="3" style="text-align: right; font-size: 14px;">SALDO KAS BERSIH:</td>
                    <td colspan="2" style="text-align: right; font-size: 16px; color: {{ $saldoBersih >= 0 ? '#047857' : '#b91c1c' }};">
                        {{ $saldoBersih >= 0 ? '' : '-' }}Rp {{ number_format(abs($saldoBersih), 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- CSS KHUSUS PRINT --}}
<style>
@media print {
    .sidebar, .topbar, .filter-card, .no-print, .btn {
        display: none !important;
    }
    body, .main-content, .page-content, span, strong, td, th, p, h1, h2, h3, h4, h5, h6, a, div {
        color: #000 !important;
    }
    body, .main-content, .page-content {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        width: 100% !important;
    }
    .print-header {
        display: block !important;
    }
    .card {
        border: none !important;
        margin-bottom: 30px !important;
        box-shadow: none !important;
    }
    .card-header {
        background: transparent !important;
        border-bottom: 2px solid #000 !important;
        padding: 0 0 10px 0 !important;
        margin-bottom: 15px !important;
    }
    .card-title {
        color: #000 !important;
        font-size: 18px !important;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }
    th, td {
        border: 1px solid #333 !important;
        padding: 8px !important;
        color: #000 !important;
        font-size: 12px !important;
    }
    th {
        background-color: #f0f0f0 !important;
        font-weight: bold !important;
    }
    tfoot tr {
        background-color: #f0f0f0 !important;
    }
    tfoot td {
        font-weight: bold !important;
    }
    td[style*="color"] {
        color: #000 !important;
    }
    th[style*="background-color"], td[style*="background-color"], tr[style*="background-color"] {
        background-color: transparent !important;
    }
    @page {
        size: A4 landscape;
        margin: 1.5cm;
    }
}
</style>

@endsection
