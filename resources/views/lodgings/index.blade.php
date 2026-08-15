@extends('layouts.app')
@section('title', 'Penginapan')
@section('page-title', 'Penginapan')
@section('page-subtitle', 'Manajemen sewa harian dan penginapan singkat')

@section('topbar-actions')
    <a href="{{ route('lodgings.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Penginapan</a>
@endsection

@section('content')

<div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="--accent-color:#7c3aed;">
        <div class="stat-icon" style="background:rgba(124,58,237,0.12);color:#a78bfa;"><i class="bi bi-moon-stars"></i></div>
        <div><div class="stat-label">Penginapan Aktif</div><div class="stat-value">{{ $activeLodgings }}</div></div>
    </div>
    <div class="stat-card" style="--accent-color:#00d4aa;">
        <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
        <div><div class="stat-label">Total Pemasukan Penginapan</div>
        <div class="stat-value small money-text">Rp {{ number_format($totalLodgingRev, 0, ',', '.') }}</div></div>
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
        <select name="status" class="form-control" style="width:140px;">
            <option value="">Semua Status</option>
            <option value="active"    {{ request('status') == 'active'    ? 'selected' : '' }}>🟢 Aktif</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✅ Selesai</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Batal</option>
        </select>
        <select name="month" class="form-control" style="width:140px;">
            <option value="">Semua Bulan</option>
            @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->setMonth($m)->translatedFormat('F') }}</option>
            @endforeach
        </select>
        <input type="number" name="year" class="form-control" style="width:90px;" value="{{ request('year') }}" placeholder="Tahun">
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filter</button>
        <a href="{{ route('lodgings.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-table"></i> Daftar Penginapan ({{ $lodgings->total() }})</div>
    </div>
    @if($lodgings->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr><th>Kamar</th><th>Penanggung Jawab</th><th>Check In</th><th>Check Out</th><th>Durasi</th><th>Tamu</th><th>Total</th><th>Bayar</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($lodgings as $l)
                <tr>
                    <td><a href="{{ route('rooms.show', $l->room) }}" style="color:var(--accent-primary); font-weight:600; text-decoration:none;">Kamar {{ $l->room->room_number }}</a></td>
                    <td>
                        <strong>{{ $l->pic_name }}</strong><br>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $l->pic_phone) }}" style="color:#25D366; font-size:12px; text-decoration:none;">
                            <i class="bi bi-whatsapp"></i> {{ $l->pic_phone }}
                        </a>
                    </td>
                    <td class="text-sm">{{ $l->check_in->translatedFormat('d-M-Y, H:i') }}</td>
                    <td class="text-sm">{{ $l->check_out->translatedFormat('d-M-Y, H:i') }}</td>
                    <td class="text-center"><span class="badge badge-info">{{ $l->duration_days }} hari</span></td>
                    <td class="text-center"><span class="badge badge-secondary"><i class="bi bi-people"></i> {{ $l->guest_count }}</span></td>
                    <td class="money-text fw-600">Rp {{ number_format($l->total_price, 0, ',', '.') }}</td>
                    <td>
                        @if($l->payment_status === 'paid')     <span class="badge badge-success">Lunas</span>
                        @elseif($l->payment_status === 'partial') <span class="badge badge-warning">Sebagian</span>
                        @else                                      <span class="badge badge-danger">Belum</span>
                        @endif
                    </td>
                    <td>
                        @if($l->status === 'active')    <span class="badge badge-success">Aktif</span>
                        @elseif($l->status === 'completed') <span class="badge badge-secondary">Selesai</span>
                        @else                               <span class="badge badge-danger">Batal</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('lodgings.show', $l) }}" class="btn btn-info btn-sm btn-icon"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('lodgings.edit', $l) }}" class="btn btn-warning btn-sm btn-icon"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('lodgings.destroy', $l) }}" method="POST" data-confirm="Hapus data penginapan ini?">
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
    <div class="pagination">{{ $lodgings->links('components.pagination') }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-moon-stars"></i>
        <p>Belum ada data penginapan</p>
        <a href="{{ route('lodgings.create') }}" class="btn btn-primary">+ Tambah Penginapan</a>
    </div>
    @endif
</div>
@endsection
