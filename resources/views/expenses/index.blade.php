@extends('layouts.app')
@section('title', 'Pengeluaran Kost')
@section('page-title', 'Pengeluaran Kost')
@section('page-subtitle', 'Rekap biaya operasional — listrik, air, internet, dll')

@section('topbar-actions')
    <a href="{{ route('system-logs.index', ['menu' => 'Pengeluaran']) }}" class="btn btn-secondary" style="background-color: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color);">
        <i class="bi bi-clock-history"></i> Log
    </a>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Pengeluaran</a>
    <a href="{{ request()->fullUrlWithQuery(['print' => 'all']) }}" class="btn btn-info"><i class="bi bi-printer"></i> Cetak</a>
@endsection

@section('content')

{{-- Month/Year Summary --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-pie-chart"></i> Pengeluaran per Kategori</div>
            <form method="GET" class="flex gap-2">
                <select name="month" class="form-control" style="width:120px;" onchange="this.form.submit()">
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('M') }}
                    </option>
                    @endforeach
                </select>
                <input type="number" name="year" class="form-control" style="width:90px;"
                    value="{{ $currentYear }}" onchange="this.form.submit()">
            </form>
        </div>

        @if($categoryTotals->count())
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach($categoryTotals as $cat)
            @php
            $pct = $totalThisMonth > 0 ? ($cat->total / $totalThisMonth * 100) : 0;
            $icons = ['listrik'=>'bi-lightning-charge','air'=>'bi-droplet','internet'=>'bi-wifi','kebersihan'=>'bi-trash','keamanan'=>'bi-shield-check','pajak'=>'bi-receipt','renovasi'=>'bi-tools','perlengkapan'=>'bi-box','lain-lain'=>'bi-three-dots'];
            $icon = $icons[$cat->category] ?? 'bi-cash';
            $labels = ['listrik'=>'Listrik','air'=>'Air/PDAM','internet'=>'Internet/WiFi','kebersihan'=>'Kebersihan','keamanan'=>'Keamanan','pajak'=>'Pajak & Admin','renovasi'=>'Renovasi','perlengkapan'=>'Perlengkapan','lain-lain'=>'Lain-lain'];
            @endphp
            <div>
                <div class="flex items-center justify-between" style="margin-bottom:5px;">
                    <span style="font-size:13px; font-weight:500; color:var(--text-secondary);">
                        <i class="{{ $icon }}" style="margin-right:6px;"></i>{{ $labels[$cat->category] ?? ($cat->category === 'deposit_deduction' ? 'Pengembalian Deposit' : $cat->category) }}
                    </span>
                    <span class="money-text fw-600" style="font-size:13px;">Rp {{ number_format($cat->total, 0, ',', '.') }}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width:{{ min(100, $pct) }}%;"></div>
                </div>
            </div>
            @endforeach

            <hr class="section-divider">
            <div class="flex items-center justify-between">
                <span class="fw-600" style="color:var(--text-primary);">Total Bulan Ini</span>
                <span class="money-text fw-700" style="font-size:18px; color:var(--accent-red);">
                    Rp {{ number_format($totalThisMonth, 0, ',', '.') }}
                </span>
            </div>
        </div>
        @else
        <div class="empty-state" style="padding:30px 0;">
            <i class="bi bi-receipt" style="font-size:36px;"></i>
            <p>Belum ada pengeluaran bulan ini</p>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-bar-chart"></i> Chart Kategori</div></div>
        <div class="chart-container">
            <canvas id="expenseChart"></canvas>
        </div>
    </div>
</div>

{{-- Filter & Table --}}
<div class="filter-bar">
    <form method="GET" class="flex items-center gap-3" style="flex:1; flex-wrap:wrap;">
        <select name="month" class="form-control" style="width:140px;">
            <option value="">Semua Bulan</option>
            @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
            </option>
            @endforeach
        </select>
        <input type="number" name="year" class="form-control" style="width:90px;" value="{{ request('year', date('Y')) }}" placeholder="Tahun">
        <select name="category" class="form-control" style="width:180px;">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-table"></i> Daftar Pengeluaran ({{ $expenses->total() }})</div>
    </div>
    @if($expenses->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Tanggal</th><th>Kategori</th><th>Judul</th><th>Periode</th><th>Nominal</th><th>Catatan</th><th class="no-print">Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($expenses as $exp)
                <tr>
                    <td class="text-sm">{{ $exp->expense_date->translatedFormat('d-M-Y') }}</td>
                    <td>
                        @php $icon = $exp->category_icon; $label = $exp->category_label; @endphp
                        <span class="badge badge-secondary"><i class="{{ $icon }}"></i> {{ $label }}</span>
                    </td>
                    <td>
                        <strong>{{ $exp->title }}</strong>
                        @if($exp->description)<br><span class="text-muted text-sm">{{ Str::limit($exp->description, 40) }}</span>@endif
                    </td>
                    <td class="text-sm fw-600">{{ \Carbon\Carbon::now()->setMonth((int)($exp->period_month))->translatedFormat('F') }} {{ $exp->period_year }}</td>
                    <td class="money-text fw-600" style="color:var(--accent-red);">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                    <td class="text-sm text-muted">{{ Str::limit($exp->notes, 50) ?: '-' }}</td>
                    <td class="no-print">
                        <div class="flex gap-2">
                            @if(isset($exp->is_deposit) && $exp->is_deposit)
                            <a href="{{ route('tenants.show', $exp->tenant_id) }}" class="btn btn-info btn-sm btn-icon" title="Lihat di Data Penyewa"><i class="bi bi-eye"></i></a>
                            <button disabled class="btn btn-secondary btn-sm btn-icon" style="opacity:0.5; cursor:not-allowed;" title="Edit via Data Penyewa"><i class="bi bi-pencil"></i></button>
                            <button disabled class="btn btn-secondary btn-sm btn-icon" style="opacity:0.5; cursor:not-allowed;" title="Hapus via Data Penyewa"><i class="bi bi-trash"></i></button>
                            @else
                            <a href="{{ route('expenses.show', $exp->id) }}" class="btn btn-info btn-sm btn-icon"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('expenses.edit', $exp->id) }}" class="btn btn-warning btn-sm btn-icon"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" data-confirm="Hapus pengeluaran ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $expenses->links('components.pagination') }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-receipt-cutoff"></i>
        <p>Belum ada data pengeluaran</p>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">+ Tambah Pengeluaran</a>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const catData = @json($categoryTotals);
const labels = {
    listrik: 'Listrik', air: 'Air/PDAM', internet: 'Internet', kebersihan: 'Kebersihan',
    keamanan: 'Keamanan', pajak: 'Pajak', renovasi: 'Renovasi', perlengkapan: 'Perlengkapan', 'lain-lain': 'Lain-lain',
    deposit_deduction: 'Pengembalian Deposit'
};
const colors = ['#00d4aa','#3b82f6','#7c3aed','#f97316','#eab308','#ef4444','#22c55e','#06b6d4','#94a3b8'];

if (catData.length) {
    new Chart(document.getElementById('expenseChart'), {
        type: 'doughnut',
        data: {
            labels: catData.map(d => labels[d.category] || d.category),
            datasets: [{
                data: catData.map(d => d.total),
                backgroundColor: colors.slice(0, catData.length),
                borderColor: 'rgba(255,255,255,0.08)',
                borderWidth: 2,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 12 } },
                tooltip: { callbacks: { label: ctx => ' Rp ' + ctx.raw.toLocaleString('id-ID') } }
            }
        }
    });
} else {
    document.getElementById('expenseChart').parentElement.innerHTML = '<div class="empty-state" style="padding:20px 0;"><i class="bi bi-pie-chart" style="font-size:36px;"></i><p>Belum ada data</p></div>';
}
</script>
@endpush
