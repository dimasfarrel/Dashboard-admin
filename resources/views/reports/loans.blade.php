@extends('layouts.app')

@section('title', 'Laporan Hutang & Piutang')
@section('page-title', 'Laporan Hutang & Piutang')
@section('page-subtitle', 'Ringkasan Transaksi Hutang dan Piutang Kost')

@push('styles')
<style>
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}
.summary-card {
    background: var(--bg-card);
    border-radius: var(--radius-md);
    padding: 20px;
    display: flex;
    align-items: center;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-card);
}
.summary-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-right: 16px;
}
.icon-income {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}
.icon-expense {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}
.icon-net {
    background: rgba(0, 212, 170, 0.15);
    color: var(--accent-primary);
}
.summary-content {
    flex: 1;
}
.summary-label {
    font-size: 13px;
    color: var(--text-secondary);
    font-weight: 500;
    margin-bottom: 4px;
}
.summary-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
}
.filter-card {
    background: var(--bg-card);
    border-radius: var(--radius-md);
    padding: 20px;
    margin-bottom: 24px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-card);
}
.filter-form {
    display: flex;
    gap: 16px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.filter-group label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-secondary);
}
.filter-group input, .filter-group select {
    padding: 10px 14px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 14px;
    outline: none;
    min-width: 160px;
    background: var(--bg-secondary);
    color: var(--text-primary);
}
.filter-group input:focus, .filter-group select:focus {
    border-color: var(--accent-primary);
    background: var(--bg-primary);
}
.btn-filter {
    padding: 10px 20px;
    background: var(--accent-primary);
    color: #000;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    transition: var(--transition);
    height: 41px;
}
.btn-filter:hover {
    background: #00e0b3;
    transform: translateY(-1px);
    box-shadow: var(--shadow-accent);
}
.badge-type {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.badge-income {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
}
.badge-expense {
    background: rgba(239, 68, 68, 0.15);
    color: #ef4444;
}
.amount-income {
    color: #10b981;
    font-weight: 600;
}
.amount-expense {
    color: #ef4444;
    font-weight: 600;
}
</style>
@endpush

@section('topbar-actions')
<button onclick="window.print()" class="btn btn-outline" style="background:white; border:1px solid var(--border-color); border-radius:8px; padding:8px 16px; cursor:pointer;">
    <i class="bi bi-printer"></i> Cetak Laporan
</button>
@endsection

@section('content')

{{-- Filter Card --}}
    <form action="{{ route('reports.loans') }}" method="GET" class="filter-form" id="filterForm">
        <div class="filter-group">
            <label>Tipe Filter</label>
            <div style="display:flex; gap:10px; margin-top:8px;">
                <label style="font-size: 13px; cursor: pointer;">
                    <input type="radio" name="filter_mode" value="month" onchange="toggleFilterMode()" {{ $filterMode === 'month' ? 'checked' : '' }}> Per Bulan
                </label>
                <label style="font-size: 13px; cursor: pointer;">
                    <input type="radio" name="filter_mode" value="date" onchange="toggleFilterMode()" {{ $filterMode === 'date' ? 'checked' : '' }}> Per Periode
                </label>
            </div>
        </div>

        <div id="filter-month-container" style="display: {{ $filterMode === 'month' ? 'flex' : 'none' }}; gap: 20px;">
            <div class="filter-group">
                <label>Bulan</label>
                <select name="month">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month', $currentMonth) == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label>Tahun</label>
                <input type="number" name="year" value="{{ request('year', $currentYear) }}" min="2020">
            </div>
        </div>

        <div id="filter-date-container" style="display: {{ $filterMode === 'date' ? 'flex' : 'none' }}; gap: 20px;">
            <div class="filter-group">
                <label>Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ request('start_date', $startDate) }}">
            </div>
            <div class="filter-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="end_date" value="{{ request('end_date', $endDate) }}">
            </div>
        </div>

        <div class="filter-group">
            <label>Jenis Transaksi</label>
            <select name="type">
                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>Semua Transaksi</option>
                <option value="receivable" {{ request('type') == 'receivable' ? 'selected' : '' }}>Hanya Pelunasan Piutang (Masuk)</option>
                <option value="payable" {{ request('type') == 'payable' ? 'selected' : '' }}>Hanya Pembayaran Hutang (Keluar)</option>
            </select>
        </div>
        <div class="filter-group">
            <button type="submit" class="btn-filter">Tampilkan</button>
        </div>
    </form>
</div>

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

{{-- Print Header (Only visible when printing) --}}
<div class="print-header">
    <h1>LAPORAN HUTANG & PIUTANG KOST</h1>
    <p>Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d-M-Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d-M-Y') }}</p>
</div>

{{-- Summary Cards --}}
<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-icon icon-income">
            <i class="bi bi-box-arrow-in-down-left"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Total Pelunasan Piutang</div>
            <div class="summary-value">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon icon-expense">
            <i class="bi bi-box-arrow-up-right"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Total Pemb. Hutang</div>
            <div class="summary-value">Rp {{ number_format($totalPayable, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon icon-net">
            <i class="bi bi-wallet-fill"></i>
        </div>
        <div class="summary-content">
            <div class="summary-label">Selisih Transaksi</div>
            <div class="summary-value">Rp {{ number_format($netCashflow, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- Transactions Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">Detail Transaksi Hutang & Piutang</h2>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 120px;">Tanggal</th>
                    <th style="width: 170px;">Kategori</th>
                    <th>Deskripsi</th>
                    <th>Keterangan</th>
                    <th style="width: 100px;">Jenis</th>
                    <th style="text-align: right; width: 180px;">Pelunasan Piutang</th>
                    <th style="text-align: right; width: 180px;">Pemb. Hutang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                    <tr>
                        <td style="color: var(--text-secondary);">{{ \Carbon\Carbon::parse($trx['date'])->translatedFormat('d-M-Y') }}</td>
                        <td>
                            <strong style="color: var(--text-primary);">{{ $trx['category'] }}</strong>
                        </td>
                        <td>
                            @if(isset($trx['route']))
                                <a href="{{ $trx['route'] }}" style="color: var(--accent-primary); text-decoration: none; border-bottom: 1px dashed var(--border-accent);">
                                    {{ $trx['description'] }}
                                </a>
                            @else
                                <span style="color: var(--text-secondary);">{{ $trx['description'] }}</span>
                            @endif
                        </td>
                        <td style="color: var(--text-secondary); font-size: 13px;">
                            {{ $trx['notes'] ?: '-' }}
                        </td>
                        <td>
                            @if($trx['type'] === 'receivable')
                                <span class="badge-type badge-income">Masuk</span>
                            @else
                                <span class="badge-type badge-expense">Keluar</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            @if($trx['type'] === 'receivable')
                                <span class="amount-income">+ Rp {{ number_format($trx['amount'], 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td style="text-align: right;">
                            @if($trx['type'] === 'payable')
                                <span class="amount-expense">- Rp {{ number_format($trx['amount'], 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                            <div style="font-size: 40px; margin-bottom: 12px;"><i class="bi bi-folder-x"></i></div>
                            <div>Belum ada transaksi pada periode ini</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Print Styles --}}
<style>
@media screen {
    .print-header {
        display: none;
    }
}
@media print {
    /* Hide UI elements not meant for print */
    .sidebar, .topbar, .filter-card, .breadcrumb, .btn-outline, .btn-icon, .page-header {
        display: none !important;
    }

    /* Reset layouts for print */
    body, html, .main-content, .page-content {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        color: #000 !important;
        width: 100% !important;
        min-height: auto !important;
    }

    /* Header for print */
    .print-header {
        display: block !important;
        text-align: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #000;
        padding-bottom: 15px;
    }
    .print-header h1 {
        font-size: 22px;
        margin: 0 0 5px 0;
        font-weight: bold;
        color: #000 !important;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .print-header p {
        font-size: 14px;
        margin: 0;
        color: #333 !important;
    }

    /* Summary Cards */
    .summary-cards {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        gap: 15px !important;
        margin-bottom: 25px !important;
        border: none !important;
        grid-template-columns: repeat(3, 1fr) !important;
    }
    .summary-card {
        flex: 1 !important;
        border: 1px solid #000 !important;
        box-shadow: none !important;
        background: #fff !important;
        padding: 15px !important;
        border-radius: 0 !important;
    }
    .summary-icon {
        display: none !important;
    }
    .summary-content {
        text-align: center !important;
    }
    .summary-label {
        font-size: 11px !important;
        color: #555 !important;
        text-transform: uppercase;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .summary-value {
        font-size: 16px !important;
        color: #000 !important;
        font-weight: bold;
    }

    /* Tables */
    .card {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    .card-header {
        display: none !important; /* Hide "Detail Transaksi" title */
    }
    .table-responsive {
        overflow: visible !important;
    }
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 11px !important;
    }
    th {
        background-color: #f2f2f2 !important;
        color: #000 !important;
        font-weight: bold !important;
        border: 1px solid #000 !important;
        padding: 10px 8px !important;
        text-transform: uppercase;
    }
    td {
        border: 1px solid #000 !important;
        padding: 10px 8px !important;
        color: #000 !important;
    }
    
    /* Clean up specific text styles */
    a {
        color: #000 !important;
        text-decoration: none !important;
        border-bottom: none !important;
    }
    strong, b {
        color: #000 !important;
    }
    .text-muted, .text-secondary, td[style*="color: var(--text-secondary)"] {
        color: #333 !important;
    }
    .badge-type, .badge {
        background: transparent !important;
        color: #000 !important;
        border: none !important;
        padding: 0 !important;
        font-weight: bold !important;
    }
    .amount-income, .amount-expense {
        color: #000 !important;
        font-weight: bold !important;
    }
    
    @page {
        size: A4 portrait;
        margin: 1.5cm;
    }
}
</style>

@endsection
