@extends('layouts.app')
@section('title', 'Laporan Pengeluaran Total')

@section('page-title', 'Laporan Pengeluaran Total')
@section('page-subtitle', 'Rekap Pengeluaran Kost dan Piutang')

@section('topbar-actions')
    <button onclick="window.print()" class="btn btn-info"><i class="bi bi-printer"></i> Cetak Laporan</button>
@endsection

@section('content')

{{-- Filter Card (Hidden on Print) --}}
<div class="card filter-card no-print" style="margin-bottom: 24px;">
    <div class="card-body">
        <form method="GET" action="{{ route('reports.total_pengeluaran') }}" class="flex items-center gap-3 flex-wrap" id="filterForm">
            <div style="display:flex; flex-direction:column; gap:4px; margin-right: 15px;">
                <label style="font-size:12px; font-weight:600; color:var(--text-secondary);">Tipe Filter</label>
                <div class="flex gap-3" style="margin-top: 5px;">
                    <label style="font-size: 13px; cursor: pointer;">
                        <input type="radio" name="filter_mode" value="month" onchange="toggleFilterMode()" {{ $filterMode === 'month' ? 'checked' : '' }}> Per Bulan
                    </label>
                    <label style="font-size: 13px; cursor: pointer;">
                        <input type="radio" name="filter_mode" value="date" onchange="toggleFilterMode()" {{ $filterMode === 'date' ? 'checked' : '' }}> Per Tanggal (Periode)
                    </label>
                </div>
            </div>

            <div id="filter-month-container" class="flex gap-3" style="display: {{ $filterMode === 'month' ? 'flex' : 'none' }};">
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label for="month" style="font-size:12px; font-weight:600; color:var(--text-secondary);">Bulan</label>
                    <select name="month" id="month" class="form-control" style="width:150px;">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ request('month', $currentMonth) == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label for="year" style="font-size:12px; font-weight:600; color:var(--text-secondary);">Tahun</label>
                    <input type="number" name="year" id="year" class="form-control" style="width:100px;" value="{{ request('year', $currentYear) }}" min="2020">
                </div>
            </div>

            <div id="filter-date-container" class="flex gap-3" style="display: {{ $filterMode === 'date' ? 'flex' : 'none' }};">
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label for="start_date" style="font-size:12px; font-weight:600; color:var(--text-secondary);">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', $startDate) }}">
                </div>
                <div style="display:flex; flex-direction:column; gap:4px;">
                    <label for="end_date" style="font-size:12px; font-weight:600; color:var(--text-secondary);">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', $endDate) }}">
                </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:4px; margin-top:20px;">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Tampilkan</button>
            </div>
        </form>

        <script>
            function toggleFilterMode() {
                const mode = document.querySelector('input[name="filter_mode"]:checked').value;
                if (mode === 'month') {
                    document.getElementById('filter-month-container').style.display = 'flex';
                    document.getElementById('filter-date-container').style.display = 'none';
                } else {
                    document.getElementById('filter-month-container').style.display = 'none';
                    document.getElementById('filter-date-container').style.display = 'flex';
                }
            }
        </script>
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
