@extends('layouts.app')
@section('title', 'Pendapatan Lain-lain')
@section('page-title', 'Pendapatan Lain-lain')
@section('page-subtitle', 'Rekap pendapatan di luar sewa dan penginapan')

@section('topbar-actions')
    <a href="{{ route('other-incomes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Pendapatan
    </a>
    <a href="{{ request()->fullUrlWithQuery(['print' => 'all']) }}" class="btn btn-info"><i class="bi bi-printer"></i> Cetak</a>
@endsection

@section('content')

{{-- Month/Year Summary --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-pie-chart"></i> Pendapatan per Kategori</div>
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
            $icons = ['parkir'=>'bi-p-circle','laundry'=>'bi-basket3','listrik'=>'bi-lightning-charge','lain-lain'=>'bi-three-dots'];
            $icon = $icons[$cat->category] ?? 'bi-cash-coin';
            $labels = ['parkir'=>'Parkir','laundry'=>'Laundry','listrik'=>'Listrik Lebih','lain-lain'=>'Lain-lain'];
            @endphp
            <div>
                <div class="flex items-center justify-between" style="margin-bottom:5px;">
                    <span style="font-size:13px; font-weight:500; color:var(--text-secondary);">
                        <i class="{{ $icon }}" style="margin-right:6px;"></i>{{ $labels[$cat->category] ?? $cat->category }}
                    </span>
                    <span class="money-text fw-600" style="font-size:13px;">Rp {{ number_format($cat->total, 0, ',', '.') }}</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" style="width:{{ min(100, $pct) }}%; background:var(--accent-primary);"></div>
                </div>
            </div>
            @endforeach

            <hr class="section-divider">
            <div class="flex items-center justify-between">
                <span class="fw-600" style="color:var(--text-primary);">Total Bulan Ini</span>
                <span class="money-text fw-700" style="font-size:18px; color:var(--accent-primary);">
                    Rp {{ number_format($totalThisMonth, 0, ',', '.') }}
                </span>
            </div>
        </div>
        @else
        <div class="empty-state" style="padding:30px 0;">
            <i class="bi bi-cash-coin" style="font-size:36px;"></i>
            <p>Belum ada pendapatan lain-lain bulan ini</p>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-bar-chart"></i> Chart Kategori</div></div>
        <div class="chart-container">
            <canvas id="incomeChart"></canvas>
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
        <input type="number" name="year" class="form-control" style="width:90px;" value="{{ request('year') }}" placeholder="Tahun">
        <select name="category" class="form-control" style="width:180px;">
            <option value="">Semua Kategori</option>
            <option value="parkir"    {{ request('category') == 'parkir'    ? 'selected' : '' }}>Parkir</option>
            <option value="laundry"   {{ request('category') == 'laundry'   ? 'selected' : '' }}>Laundry</option>
            <option value="listrik"   {{ request('category') == 'listrik'   ? 'selected' : '' }}>Listrik Lebih</option>
            <option value="lain-lain" {{ request('category') == 'lain-lain' ? 'selected' : '' }}>Lain-lain</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button>
        <a href="{{ route('other-incomes.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-table"></i> Daftar Pendapatan ({{ $incomes->total() }})</div>
    </div>
    @if($incomes->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Tanggal</th><th>Judul</th><th>Kategori</th><th>Periode</th><th>Nominal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($incomes as $inc)
                <tr>
                    <td class="text-sm">{{ $inc->income_date->translatedFormat('d-M-Y') }}</td>
                    <td>
                        <strong>{{ $inc->title }}</strong>
                        @if($inc->notes)<br><span class="text-muted text-sm">{{ Str::limit($inc->notes, 40) }}</span>@endif
                    </td>
                    <td>
                        <span class="badge badge-secondary"><i class="{{ $inc->category_icon }}"></i> {{ $inc->category_label }}</span>
                    </td>
                    <td class="text-sm fw-600">{{ \Carbon\Carbon::now()->setMonth((int)($inc->period_month))->translatedFormat('F') }} {{ $inc->period_year }}</td>
                    <td class="money-text fw-600" style="color:var(--accent-primary);">Rp {{ number_format($inc->amount, 0, ',', '.') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('other-incomes.show', $inc) }}" class="btn btn-info btn-sm btn-icon"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('other-incomes.edit', $inc) }}" class="btn btn-warning btn-sm btn-icon"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('other-incomes.destroy', $inc) }}" method="POST" data-confirm="Hapus pendapatan ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $incomes->links('components.pagination') }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-cash-coin"></i>
        <p>Belum ada data pendapatan lain-lain</p>
        <a href="{{ route('other-incomes.create') }}" class="btn btn-primary">+ Tambah Pendapatan</a>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const catData = @json($categoryTotals);
const colors = ['#00d4aa','#3b82f6','#7c3aed','#f97316','#eab308','#ef4444'];
const labels = { parkir:'Parkir', laundry:'Laundry', listrik:'Listrik Lebih', 'lain-lain':'Lain-lain' };

if (catData.length) {
    new Chart(document.getElementById('incomeChart'), {
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
    const el = document.getElementById('incomeChart');
    if (el) el.parentElement.innerHTML = '<div class="empty-state" style="padding:20px 0;"><i class="bi bi-cash-coin" style="font-size:36px;"></i><p>Belum ada data</p></div>';
}
</script>
@endpush
