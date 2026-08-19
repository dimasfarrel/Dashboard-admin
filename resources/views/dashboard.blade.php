@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan Admin Kost Malang — ' . \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->translatedFormat('F Y'))

@section('topbar-actions')
    {{-- Month/Year Selector --}}
    <form method="GET" action="{{ route('dashboard') }}" class="flex items-center gap-2" id="dashMonthForm">
        <select name="month" class="form-control" style="width:130px; padding:6px 10px; font-size:13px;" onchange="document.getElementById('dashMonthForm').submit()">
            @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
            </option>
            @endforeach
        </select>
        <input type="number" name="year" class="form-control" style="width:85px; padding:6px 10px; font-size:13px;"
            value="{{ $currentYear }}" min="2020" max="{{ now()->year + 1 }}" onchange="document.getElementById('dashMonthForm').submit()">
    </form>
    <a href="{{ route('system-logs.index') }}" class="btn btn-secondary btn-sm" style="background-color: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color);">
        <i class="bi bi-clock-history"></i> Riwayat Aktivitas (Log)
    </a>
    <a href="{{ route('reports.total_omzet') }}" class="btn btn-info btn-sm">
        <i class="bi bi-printer"></i> Cetak Omzet Periode
    </a>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Tambah Kamar
    </a>
@endsection

@section('content')

{{-- ===== STAT CARDS ===== --}}
<div class="stat-grid">
    {{-- Total Kamar --}}
    <div class="stat-card" style="--accent-color:#00d4aa; --icon-rgb:0,212,170;">
        <div class="stat-icon"><i class="bi bi-building"></i></div>
        <div>
            <div class="stat-label">Total Kamar</div>
            <div class="stat-value">{{ $totalRooms }}</div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <span class="badge badge-success">
                <span class="room-status-dot available"></span>{{ $availableRooms }} Tersedia
            </span>
            <span class="badge badge-danger">
                <span class="room-status-dot occupied"></span>{{ $occupiedRooms }} Dihuni
            </span>
            @if($maintenanceRooms > 0)
            <span class="badge badge-warning">{{ $maintenanceRooms }} Maintenance</span>
            @endif
        </div>
    </div>

    {{-- Omzet Bulan Ini --}}
    <div class="stat-card" style="--accent-color:#22c55e; --icon-rgb:34,197,94;">
        <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-label">Omzet {{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->translatedFormat('M Y') }}</div>
            <div class="stat-value small money-text">Rp {{ number_format($monthlyRevenue + $lodgingRevenue + $otherIncomeRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="text-muted text-sm" style="flex-wrap:wrap; display:flex; gap:4px;">
            <span>Sewa: Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}</span>
            <span>&nbsp;|&nbsp;</span>
            <span>Penginapan: Rp {{ number_format($lodgingRevenue, 0, ',', '.') }}</span>
            @if($otherIncomeRevenue > 0)
            <span>&nbsp;|&nbsp;</span>
            <span>Lain-lain: Rp {{ number_format($otherIncomeRevenue, 0, ',', '.') }}</span>
            @endif
            @if($depositRevenue > 0)
            <span>&nbsp;|&nbsp;</span>
            <span style="color:#eab308;">Deposit (titipan): Rp {{ number_format($depositRevenue, 0, ',', '.') }}</span>
            @endif
        </div>
    </div>

    {{-- Pengeluaran Bulan Ini --}}
    <div class="stat-card" style="--accent-color:#ef4444; --icon-rgb:239,68,68;">
        <div class="stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
        <div>
            <div class="stat-label">Pengeluaran {{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->translatedFormat('M Y') }}</div>
            <div class="stat-value small money-text">Rp {{ number_format($monthlyExpenses + $maintenanceCost, 0, ',', '.') }}</div>
        </div>
        <div class="text-muted text-sm">
            Operasional: Rp {{ number_format($monthlyExpenses, 0, ',', '.') }} &nbsp;|&nbsp;
            Maintenance: Rp {{ number_format($maintenanceCost, 0, ',', '.') }}
        </div>
    </div>

    {{-- Net Bulan Ini --}}
    @php $net = ($monthlyRevenue + $lodgingRevenue + $otherIncomeRevenue) - ($monthlyExpenses + $maintenanceCost); @endphp
    <div class="stat-card" style="--accent-color:{{ $net >= 0 ? '#3b82f6' : '#f97316' }}; --icon-rgb:{{ $net >= 0 ? '59,130,246' : '249,115,22' }};">
        <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-label">Profit Bersih</div>
            <div class="stat-value small money-text {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format(abs($net), 0, ',', '.') }}
            </div>
        </div>
        <div class="text-muted text-sm">{{ $net >= 0 ? '▲ Surplus' : '▼ Defisit' }} bulan {{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->translatedFormat('M Y') }}</div>
    </div>

    {{-- Pembayaran Pending --}}
    <div class="stat-card" style="--accent-color:#eab308; --icon-rgb:234,179,8;">
        <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
        <div>
            <div class="stat-label">Pembayaran Pending</div>
            <div class="stat-value">{{ $pendingPayments }}</div>
        </div>
        @if($overduePayments > 0)
        <span class="badge badge-danger"><i class="bi bi-exclamation-triangle"></i> {{ $overduePayments }} Terlambat</span>
        @else
        <span class="badge badge-success"><i class="bi bi-check-circle"></i> Tidak ada yang terlambat</span>
        @endif
    </div>

    {{-- Penginapan Aktif --}}
    <div class="stat-card" style="--accent-color:#7c3aed; --icon-rgb:124,58,237;">
        <div class="stat-icon"><i class="bi bi-moon-stars"></i></div>
        <div>
            <div class="stat-label">Penginapan Aktif</div>
            <div class="stat-value">{{ $activeLodgings }}</div>
        </div>
        @if($pendingMaintenance > 0)
        <span class="badge badge-warning"><i class="bi bi-wrench"></i> {{ $pendingMaintenance }} Maintenance pending</span>
        @else
        <span class="text-muted text-sm">Semua kamar kondisi baik</span>
        @endif
    </div>
</div>

{{-- ===== CHART + RECENT ===== --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

    {{-- Revenue Chart --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-bar-chart-line"></i> Omzet 6 Bulan Terakhir</div>
        </div>
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Room Occupancy Donut --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-pie-chart"></i> Status Hunian</div>
        </div>
        <div style="display:flex; align-items:center; gap:24px; height:200px; justify-content:center;">
            <canvas id="occupancyChart" width="160" height="160" style="max-width:160px;"></canvas>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div class="flex items-center gap-2">
                    <div style="width:12px;height:12px;border-radius:3px;background:#00d4aa;flex-shrink:0;"></div>
                    <span class="text-sm text-muted">Tersedia</span>
                    <span class="fw-600 text-primary" style="margin-left:auto;">{{ $availableRooms }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div style="width:12px;height:12px;border-radius:3px;background:#ef4444;flex-shrink:0;"></div>
                    <span class="text-sm text-muted">Dihuni</span>
                    <span class="fw-600 text-primary" style="margin-left:auto;">{{ $occupiedRooms }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div style="width:12px;height:12px;border-radius:3px;background:#eab308;flex-shrink:0;"></div>
                    <span class="text-sm text-muted">Maintenance</span>
                    <span class="fw-600 text-primary" style="margin-left:auto;">{{ $maintenanceRooms }}</span>
                </div>
                <hr class="section-divider" style="margin:4px 0;">
                @if($totalRooms > 0)
                <div class="text-sm text-muted">
                    Tingkat Hunian: <span class="fw-700 text-success">{{ $totalRooms > 0 ? round($occupiedRooms/$totalRooms*100) : 0 }}%</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== RECENT ROWS ===== --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    {{-- Recent Rooms --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-door-open"></i> Kamar Terbaru</div>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        @if($recentRooms->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No. Kamar</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Penyewa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentRooms as $room)
                    <tr>
                        <td>
                            <a href="{{ route('rooms.show', $room) }}" style="color:var(--accent-primary); text-decoration:none; font-weight:600;">
                                Kamar {{ $room->room_number }}
                            </a>
                        </td>
                        <td class="money-text">Rp {{ number_format($room->price, 0, ',', '.') }}</td>
                        <td>
                            @php $badge = $room->status_badge; @endphp
                            <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        </td>
                        <td>{{ $room->tenant?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <i class="bi bi-door-closed"></i>
            <p>Belum ada data kamar</p>
            <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm">+ Tambah Kamar</a>
        </div>
        @endif
    </div>

    {{-- Recent Payments --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-cash-coin"></i> Pembayaran Terbaru</div>
            <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        @if($recentPayments->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Penyewa</th>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPayments as $payment)
                    <tr>
                        <td>
                            <strong>{{ $payment->tenant?->name ?? '—' }}</strong><br>
                            <span class="text-muted text-sm">Kamar {{ $payment->room?->room_number }}</span>
                        </td>
                        <td class="text-sm">{{ $payment->period_label }}</td>
                        <td class="money-text">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($payment->status === 'paid')
                                <span class="badge badge-success">Lunas</span>
                            @elseif($payment->status === 'overdue')
                                <span class="badge badge-danger">Terlambat</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <i class="bi bi-receipt"></i>
            <p>Belum ada data pembayaran</p>
            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">+ Catat Pembayaran</a>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';

// Revenue Chart
const rCtx = document.getElementById('revenueChart').getContext('2d');
const chartData = @json($revenueChart);

new Chart(rCtx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => d.label),
        datasets: [
            {
                label: 'Pemasukan',
                data: chartData.map(d => d.revenue),
                backgroundColor: 'rgba(0,212,170,0.7)',
                borderColor: '#00d4aa',
                borderWidth: 1,
                borderRadius: 6,
            },
            {
                label: 'Pengeluaran',
                data: chartData.map(d => d.expense),
                backgroundColor: 'rgba(239,68,68,0.6)',
                borderColor: '#ef4444',
                borderWidth: 1,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: ctx => ' Rp ' + ctx.raw.toLocaleString('id-ID')
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: {
                grid: { color: 'rgba(255,255,255,0.04)' },
                ticks: {
                    font: { size: 10 },
                    callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt'
                }
            }
        }
    }
});

// Occupancy Donut
const oCtx = document.getElementById('occupancyChart').getContext('2d');
new Chart(oCtx, {
    type: 'doughnut',
    data: {
        labels: ['Tersedia', 'Dihuni', 'Maintenance'],
        datasets: [{
            data: [{{ $availableRooms }}, {{ $occupiedRooms }}, {{ $maintenanceRooms }}],
            backgroundColor: ['rgba(0,212,170,0.8)', 'rgba(239,68,68,0.8)', 'rgba(234,179,8,0.8)'],
            borderColor: ['#00d4aa', '#ef4444', '#eab308'],
            borderWidth: 2,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: false,
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw + ' kamar' } }
        }
    }
});
</script>
@endpush

