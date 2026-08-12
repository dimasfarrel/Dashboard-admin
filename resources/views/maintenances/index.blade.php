@extends('layouts.app')
@section('title', 'Maintenance Kamar')
@section('page-title', 'Maintenance Kamar')
@section('page-subtitle', 'Rekap perbaikan dan perawatan per kamar')

@section('topbar-actions')
    <a href="{{ route('maintenances.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Laporkan Maintenance</a>
@endsection

@section('content')

<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="--accent-color:#ef4444;">
        <div class="stat-icon" style="background:rgba(239,68,68,0.12);color:#ef4444;"><i class="bi bi-cash"></i></div>
        <div><div class="stat-label">Total Biaya Maintenance</div>
        <div class="stat-value small money-text">Rp {{ number_format($totalCost, 0, ',', '.') }}</div></div>
    </div>
    <div class="stat-card" style="--accent-color:#eab308;">
        <div class="stat-icon" style="background:rgba(234,179,8,0.12);color:#eab308;"><i class="bi bi-hourglass"></i></div>
        <div><div class="stat-label">Pending</div><div class="stat-value">{{ $pending }}</div></div>
    </div>
    <div class="stat-card" style="--accent-color:#3b82f6;">
        <div class="stat-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;"><i class="bi bi-gear-wide-connected"></i></div>
        <div><div class="stat-label">Sedang Proses</div><div class="stat-value">{{ $inProgress }}</div></div>
    </div>
</div>

{{-- Filter --}}
<div class="filter-bar">
    <form method="GET" class="flex items-center gap-3" style="flex:1; flex-wrap:wrap;">
        <select name="room_id" class="form-control" style="width:160px;">
            <option value="">Semua Kamar</option>
            @foreach($rooms as $r)
            <option value="{{ $r->id }}" {{ request('room_id') == $r->id ? 'selected' : '' }}>Kamar {{ $r->room_number }}</option>
            @endforeach
        </select>
        <select name="category" class="form-control" style="width:180px;">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-control" style="width:140px;">
            <option value="">Semua Status</option>
            <option value="pending"     {{ request('status') == 'pending'     ? 'selected' : '' }}>⏳ Pending</option>
            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>🔵 Proses</option>
            <option value="done"        {{ request('status') == 'done'        ? 'selected' : '' }}>✅ Selesai</option>
            <option value="cancelled"   {{ request('status') == 'cancelled'   ? 'selected' : '' }}>❌ Batal</option>
        </select>
        <select name="month" class="form-control" style="width:130px;">
            <option value="">Semua Bulan</option>
            @for($i=1; $i<=12; $i++)
                <option value="{{ $i }}" {{ (isset($month) && $month == $i) ? 'selected' : '' }}>{{ \Carbon\Carbon::now()->setMonth((int)($i))->translatedFormat('F') }}</option>
            @endfor
        </select>
        <input type="number" name="year" class="form-control" style="width:90px;" value="{{ request('year') }}" placeholder="Tahun">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button>
        <a href="{{ route('maintenances.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-tools"></i> Daftar Maintenance ({{ $maintenances->total() }})</div>
    </div>
    @if($maintenances->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Kamar</th><th>Item</th><th>Kategori</th><th>Biaya</th><th>Vendor</th><th>Tgl Lapor</th><th>Selesai</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($maintenances as $m)
                <tr>
                    <td><a href="{{ route('rooms.show', $m->room) }}" style="color:var(--accent-primary); text-decoration:none; font-weight:600;">Kamar {{ $m->room->room_number }}</a></td>
                    <td><strong>{{ $m->item_name }}</strong><br><span class="text-muted text-sm">{{ Str::limit($m->description, 40) }}</span></td>
                    <td><span class="badge badge-secondary">{{ $m->category_label }}</span></td>
                    <td class="money-text">Rp {{ number_format($m->cost, 0, ',', '.') }}</td>
                    <td>{{ $m->vendor ?? '—' }}</td>
                    <td class="text-sm">{{ $m->report_date->format('d/m/Y') }}</td>
                    <td class="text-sm">{{ $m->done_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        @if($m->status === 'done')        <span class="badge badge-success">Selesai</span>
                        @elseif($m->status === 'in_progress') <span class="badge badge-info">Proses</span>
                        @elseif($m->status === 'pending')     <span class="badge badge-warning">Pending</span>
                        @else                                  <span class="badge badge-secondary">Batal</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('maintenances.show', $m) }}" class="btn btn-info btn-sm btn-icon"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('maintenances.edit', $m) }}" class="btn btn-warning btn-sm btn-icon"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('maintenances.destroy', $m) }}" method="POST" data-confirm="Hapus data maintenance ini?">
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
    <div class="pagination">{{ $maintenances->links('components.pagination') }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-tools"></i>
        <p>Belum ada data maintenance</p>
        <a href="{{ route('maintenances.create') }}" class="btn btn-primary">+ Laporkan Maintenance</a>
    </div>
    @endif
</div>
@endsection
