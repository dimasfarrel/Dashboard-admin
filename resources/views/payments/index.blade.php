@extends('layouts.app')
@section('title', 'Omzet & Pembayaran')
@section('page-title', 'Omzet & Pembayaran')
@section('page-subtitle', 'Rekap pembayaran sewa & penginapan seluruh kamar')


@section('topbar-actions')
    <a href="{{ route('payments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Catat Pembayaran
    </a>
@endsection

@section('content')

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="--accent-color:#00d4aa; display:flex; flex-direction:column; justify-content:space-between; min-height:100px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-label">Total Pemasukan (Lunas)</div>
                <div class="stat-value small money-text">Rp {{ number_format($totalPaid + $totalLodgingPaid + $totalOtherPaid + $totalDepositPaid, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="text-muted text-xs" style="margin-top:8px; border-top:1px solid rgba(255,255,255,0.08); padding-top:6px; display:flex; flex-wrap:wrap; gap:8px;">
            <span>Sewa: Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
            <span style="opacity:0.5;">|</span>
            <span>Penginapan: Rp {{ number_format($totalLodgingPaid, 0, ',', '.') }}</span>
            <span style="opacity:0.5;">|</span>
            <span>Lainnya: Rp {{ number_format($totalOtherPaid, 0, ',', '.') }}</span>
            @if($totalDepositPaid > 0)
            <span style="opacity:0.5;">|</span>
            <span style="color:#eab308;">Deposit: Rp {{ number_format($totalDepositPaid, 0, ',', '.') }}</span>
            @endif
        </div>
    </div>
    <div class="stat-card" style="--accent-color:#eab308;">
        <div class="stat-icon" style="background:rgba(234,179,8,0.12);color:#eab308;"><i class="bi bi-clock"></i></div>
        <div><div class="stat-label">Belum Dibayar</div>
        <div class="stat-value">{{ $totalPending }}</div></div>
    </div>
    <div class="stat-card" style="--accent-color:#ef4444;">
        <div class="stat-icon" style="background:rgba(239,68,68,0.12);color:#ef4444;"><i class="bi bi-exclamation-circle"></i></div>
        <div><div class="stat-label">Terlambat</div>
        <div class="stat-value">{{ $totalOverdue }}</div></div>
    </div>
</div>

{{-- Due Day Info & Setting --}}
<div class="card due-day-card" style="margin-bottom:16px; border-color:rgba(234,179,8,0.3); background:rgba(234,179,8,0.04);">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:36px; height:36px; border-radius:10px; background:rgba(234,179,8,0.15); display:flex; align-items:center; justify-content:center;">
                <i class="bi bi-calendar-event" style="color:#eab308; font-size:18px;"></i>
            </div>
            <div>
                <div style="font-weight:700; color:var(--text-primary); font-size:14px;">Jatuh Tempo: Tanggal <span style="color:#eab308;">{{ $dueDay }}</span> setiap bulan</div>
                <div class="text-muted text-sm">Pembayaran yang melewati tanggal ini otomatis ditandai <span style="color:var(--accent-red);">Terlambat</span></div>
            </div>
        </div>
        <form action="{{ route('payments.update-due-day') }}" method="POST" class="flex items-center gap-2">
            @csrf @method('PATCH')
            <label class="text-sm text-muted">Ubah ke tanggal:</label>
            <input type="number" name="due_day" class="form-control" style="width:70px; padding:6px 10px; font-size:13px;"
                value="{{ $dueDay }}" min="1" max="28" required>
            <button type="submit" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Simpan</button>
        </form>
    </div>
</div>


<div class="filter-bar" style="display:flex; flex-direction:column; gap:12px;">
    <form method="GET" id="filterForm">
        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            
            <!-- Filter Periode Sewa -->
            <div>
                <label class="text-xs text-muted" style="display:block; margin-bottom:4px;">Periode Sewa</label>
                <div style="display:flex; gap:8px;">
                    <select name="period_month" class="form-control" style="width:130px;">
                        <option value="">Semua Bulan</option>
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('period_month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                    <input type="number" name="period_year" class="form-control" style="width:90px;"
                        value="{{ request('period_year') }}" placeholder="Tahun">
                </div>
            </div>

            <!-- Filter Tanggal Bayar -->
            <div>
                <label class="text-xs text-muted" style="display:block; margin-bottom:4px;">Tgl Bayar Masuk</label>
                <div style="display:flex; gap:8px;">
                    <select name="pay_month" class="form-control" style="width:130px;">
                        <option value="">Semua Bulan</option>
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('pay_month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                    <input type="number" name="pay_year" class="form-control" style="width:90px;"
                        value="{{ request('pay_year') }}" placeholder="Tahun">
                </div>
            </div>

            <!-- Filter Kamar -->
            <div>
                <label class="text-xs text-muted" style="display:block; margin-bottom:4px;">Kamar</label>
                <select name="room_id" class="form-control" style="width:110px;">
                    <option value="">Semua</option>
                    @foreach($rooms ?? [] as $r)
                        <option value="{{ $r->id }}" {{ request('room_id') == $r->id ? 'selected' : '' }}>Kamar {{ $r->room_number }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Tipe -->
            <div>
                <label class="text-xs text-muted" style="display:block; margin-bottom:4px;">Tipe</label>
                <select name="type" class="form-control" style="width:110px;">
                    <option value="">Semua</option>
                    <option value="rental" {{ request('type') == 'rental' ? 'selected' : '' }}>Sewa Bulanan</option>
                    <option value="lodging" {{ request('type') == 'lodging' ? 'selected' : '' }}>Harian</option>
                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Lainnya</option>
                    <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposit Jaminan</option>
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="text-xs text-muted" style="display:block; margin-bottom:4px;">Status</label>
                <select name="status" class="form-control" style="width:120px;">
                    <option value="">Semua</option>
                    <option value="paid"    {{ request('status') == 'paid'    ? 'selected' : '' }}>✅ Lunas</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>🔴 Terlambat</option>
                </select>
            </div>

            <!-- Buttons -->
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Reset</a>
                <a href="{{ request()->fullUrlWithQuery(['print' => 'all']) }}" target="_blank" class="btn btn-info"><i class="bi bi-printer"></i> Cetak</a>
            </div>
        </div>
    </form>
</div>

<style>
@media print {
    .sidebar, .topbar, .filter-bar, .due-day-card, .card-header .btn, td:last-child, th:last-child, .stat-icon, .btn {
        display: none !important;
    }
    body { background: white !important; padding: 0 !important; color: black !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; margin: 0 !important; }
    .table-wrapper { overflow: visible !important; }
    table { border-collapse: collapse !important; width: 100% !important; }
    th, td { border: 1px solid #ccc !important; padding: 8px !important; color: black !important; }
    .badge { border: 1px solid #666 !important; color: black !important; background: none !important; }
    .stat-card { border: 1px solid #ccc !important; box-shadow: none !important; background: white !important; color: black !important; }
    .stat-label { color: #666 !important; }
    .stat-value { color: black !important; }
}
</style>

{{-- Table --}}
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-table"></i> Riwayat Transaksi Masuk / Omzet Kost ({{ $paginatedTransactions->total() }})</div>
    </div>
    @if($paginatedTransactions->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tipe</th>
                    <th>Kamar</th>
                    <th>Nama Pelanggan</th>
                    <th>Periode / Tgl</th>
                    <th>Nominal</th>
                    <th>Metode</th>
                    <th>Jatuh Tempo</th>
                    <th>Tgl Bayar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paginatedTransactions as $trx)
                <tr>
                    <td>
                        @if($trx['type'] === 'rental')
                            <span class="badge" style="background:rgba(0,212,170,0.12); color:#00d4aa; border:1px solid rgba(0,212,170,0.2);">Sewa</span>
                        @elseif($trx['type'] === 'lodging')
                            <span class="badge" style="background:rgba(59,130,246,0.12); color:#3b82f6; border:1px solid rgba(59,130,246,0.2);">Harian</span>
                        @elseif($trx['type'] === 'deposit')
                            <span class="badge" style="background:rgba(234,179,8,0.12); color:#eab308; border:1px solid rgba(234,179,8,0.2);">Deposit</span>
                        @else
                            <span class="badge" style="background:rgba(168,85,247,0.12); color:#a855f7; border:1px solid rgba(168,85,247,0.2);">Lainnya</span>
                        @endif
                    </td>
                    <td><strong>Kamar {{ $trx['room'] }}</strong></td>
                    <td>{{ $trx['name'] }}</td>
                    <td class="text-sm">{{ $trx['period'] }}</td>
                    <td class="money-text">Rp {{ number_format($trx['amount'], 0, ',', '.') }}</td>
                    <td>{{ ucfirst($trx['method']) }}</td>
                    <td class="text-sm">
                        @if($trx['due_date'])
                            @php $dueDate = \Carbon\Carbon::parse($trx['due_date']); $isPast = $dueDate->isPast() && $trx['status'] !== 'paid'; @endphp
                            <span style="color:{{ $isPast ? 'var(--accent-red)' : 'var(--text-muted)' }};">
                                {{ $dueDate->translatedFormat('d-M-Y') }}
                                @if($isPast) <i class="bi bi-exclamation-triangle"></i>@endif
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-sm">
                        @if($trx['date'])
                            {{ \Carbon\Carbon::parse($trx['date'])->translatedFormat('d-M-Y') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($trx['status'] === 'paid')     <span class="badge badge-success">Lunas</span>
                        @elseif($trx['status'] === 'overdue') <span class="badge badge-danger">Terlambat</span>
                        @else                               <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-2">
                            @if(isset($trx['is_virtual']) && $trx['is_virtual'])
                                <a href="{{ $trx['link'] }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Catat</a>
                            @else
                                <a href="{{ $trx['link'] }}" class="btn btn-info btn-sm btn-icon"><i class="bi bi-eye"></i></a>
                                <a href="{{ $trx['edit_link'] }}" class="btn btn-warning btn-sm btn-icon"><i class="bi bi-pencil"></i></a>
                                @if($trx['type'] === 'rental')
                                <form action="{{ route('payments.destroy', $trx['id']) }}" method="POST" data-confirm="Hapus data pembayaran ini?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="bi bi-trash"></i></button>
                                </form>
                                @elseif($trx['type'] === 'lodging')
                                <form action="{{ route('lodgings.destroy', $trx['id']) }}" method="POST" data-confirm="Hapus data penginapan ini?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $paginatedTransactions->links('components.pagination') }}</div>
    @else
    <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
        <i class="bi bi-cash-coin" style="font-size:48px; display:block; margin-bottom:12px;"></i>
        Belum ada riwayat transaksi masuk yang sesuai filter.
    </div>
    @endif
</div>
@endsection
