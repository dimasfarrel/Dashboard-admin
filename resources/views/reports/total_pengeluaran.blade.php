@extends('layouts.app')
@section('title', 'Laporan Pengeluaran Total')

@section('page-title', 'Laporan Pengeluaran Total')
@section('page-subtitle', 'Rekap Pengeluaran Kost dan Piutang')

@push('styles')
<style>
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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
.icon-expense { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
.icon-receivable { background: rgba(14, 165, 233, 0.15); color: #0ea5e9; }
.icon-net { background: rgba(168, 85, 247, 0.15); color: #a855f7; }
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
/* Ensure table cells don't wrap and have enough space */
.table th, .table td {
    white-space: nowrap;
}
</style>
@endpush

@section('topbar-actions')
    <button onclick="window.print()" class="btn btn-info"><i class="bi bi-printer"></i> Cetak Laporan</button>
@endsection

@section('content')

{{-- Filter Card (Hidden on Print) --}}
<div class="card filter-card no-print" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.total_pengeluaran') }}" class="flex items-center gap-3 flex-wrap">
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

{{-- Print Header (Only visible on Print) --}}
<div class="print-header" style="display: none; text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px;">
    <h1 style="margin: 0; font-size: 24px; font-weight: bold; text-transform: uppercase;">Laporan Pengeluaran Total Kost Malang</h1>
    <p style="margin: 5px 0 0 0; font-size: 14px;">
        Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
    </p>
    <p style="margin: 2px 0 0 0; font-size: 12px; color: #555;">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
</div>

{{-- 2. TOTAL PENGELUARAN --}}
@php
    $sumPengeluaran = $expenses->sum('pengeluaran_amount');
    $sumPiutang = $expenses->sum('piutang_amount');
    $sumTotalPengeluaran = $sumPengeluaran + $sumPiutang;
@endphp

{{-- Summary Cards --}}
<div class="summary-cards no-print">
    <div class="summary-card">
        <div class="summary-icon icon-expense">
            <i class="bi bi-cart-dash"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Total Pengeluaran</div>
            <div class="summary-value">Rp {{ number_format($sumPengeluaran, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon icon-receivable">
            <i class="bi bi-journal-minus"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Total Piutang</div>
            <div class="summary-value">Rp {{ number_format($sumPiutang, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon icon-net">
            <i class="bi bi-box-arrow-up-right"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Total Pengeluaran Keseluruhan</div>
            <div class="summary-value">Rp {{ number_format($sumTotalPengeluaran, 0, ',', '.') }}</div>
        </div>
    </div>
</div>
<div class="card" style="margin-bottom: 30px; border:1px solid #ef4444;">
    <div class="card-header" style="background-color: rgba(239, 68, 68, 0.1); border-bottom: 1px solid #ef4444;">
        <h2 class="card-title" style="color: #b91c1c; font-weight: 700;"><i class="bi bi-arrow-up-right-circle"></i> Pengeluaran Total</h2>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr style="background-color: #f8fafc;">
                    <th style="width: 100px;">Tanggal</th>
                    <th style="width: 130px;">Kategori</th>
                    <th>Deskripsi</th>
                    <th style="text-align: right; width: 140px;">Nominal Pengeluaran</th>
                    <th style="text-align: right; width: 140px;">Nominal Piutang</th>
                    <th style="text-align: right; width: 140px; background-color: #fef2f2;">Total Keluar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    @php $rowTotalExp = $exp['pengeluaran_amount'] + $exp['piutang_amount']; @endphp
                    <tr>
                        <td style="color: #475569;">{{ \Carbon\Carbon::parse($exp['date'])->translatedFormat('d-M-Y') }}</td>
                        <td><strong>{{ $exp['category'] }}</strong></td>
                        <td>{{ $exp['description'] }}</td>
                        <td style="text-align: right; color: #b91c1c;">{{ $exp['pengeluaran_amount'] > 0 ? 'Rp ' . number_format($exp['pengeluaran_amount'], 0, ',', '.') : '-' }}</td>
                        <td style="text-align: right; color: #1d4ed8;">{{ $exp['piutang_amount'] > 0 ? 'Rp ' . number_format($exp['piutang_amount'], 0, ',', '.') : '-' }}</td>
                        <td style="text-align: right; font-weight: bold; background-color: rgba(239, 68, 68, 0.05);">Rp {{ number_format($rowTotalExp, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">Belum ada data pengeluaran pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #fef2f2;">
                    <td colspan="3" style="text-align: right;">TOTAL:</td>
                    <td style="text-align: right; color: #b91c1c;">Rp {{ number_format($sumPengeluaran, 0, ',', '.') }}</td>
                    <td style="text-align: right; color: #1d4ed8;">Rp {{ number_format($sumPiutang, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-size: 15px;">Rp {{ number_format($sumTotalPengeluaran, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- CSS KHUSUS PRINT --}}
<style>
@media print {
    /* Sembunyikan elemen UI yang tidak perlu dicetak */
    .sidebar, .topbar, .filter-card, .no-print, .btn {
        display: none !important;
    }

    /* Reset margin dan padding untuk area print */
    body, .main-content, .page-content {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        width: 100% !important;
    }

    /* Tampilkan header khusus print */
    .print-header {
        display: block !important;
    }

    /* Gaya tabel agar rapi saat diprint (Hitam Putih / Grayscale) */
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

    /* Hapus warna background dan teks warna-warni agar cetakan bersih */
    td[style*="color"] {
        color: #000 !important;
    }
    th[style*="background-color"], td[style*="background-color"], tr[style*="background-color"] {
        background-color: transparent !important;
    }

    @page {
        size: A4 portrait;
        margin: 1.5cm;
    }
}
</style>

@endsection
