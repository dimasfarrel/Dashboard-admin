@extends('layouts.app')
@section('title', 'Data Penyewa')
@section('page-title', 'Data Penyewa')
@section('page-subtitle', 'Daftar semua penyewa kost aktif dan tidak aktif')

@section('topbar-actions')
    <a href="{{ route('system-logs.index', ['menu' => 'Penyewa']) }}" class="btn btn-secondary btn-sm" style="background-color: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color);">
        <i class="bi bi-clock-history"></i> Log Penyewa
    </a>
    <a href="{{ route('tenants.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus-fill"></i> Tambah Penyewa</a>
@endsection

@section('content')

<!-- Filter Bar -->
<div class="filter-bar" style="margin-bottom: 20px; background: var(--bg-card); padding: 15px; border-radius: 12px; border: 1px solid var(--border-accent, #334155);">
    <form method="GET" action="{{ route('tenants.index') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <div>
            <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block;">Status Penyewa</label>
            <select name="status" class="form-control" style="width: 150px;">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
        </div>
        <div>
            <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block;">Cari Nama/Kamar</label>
            <input type="text" name="search" class="form-control" style="width: 200px;" value="{{ request('search') }}" placeholder="Ketik nama atau nomor kamar...">
        </div>
        <div>
            <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block;">Cari Orang Tua / Wali</label>
            <input type="text" name="parent_search" class="form-control" style="width: 200px;" value="{{ request('parent_search') }}" placeholder="Ketik nama orang tua...">
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-people"></i> Semua Penyewa ({{ $tenants->total() }})</div>
    </div>
    @if($tenants->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama Penyewa</th>
                    <th>No. KTP</th>
                    <th>WhatsApp</th>
                    <th>Kamar</th>
                    <th>Mulai Sewa</th>
                    <th>Deposit</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tenants as $tenant)
                <tr>
                    <td>
                        <div style="font-weight:600; color:var(--text-primary);">
                            {{ $tenant->name }}
                            @if($tenant->nickname) <span style="font-weight:400; color:var(--text-secondary);">("{{ $tenant->nickname }}")</span> @endif
                        </div>
                        <div class="text-muted text-sm">{{ $tenant->occupation ?? '—' }} · {{ $tenant->origin_city ?? '' }}</div>
                    </td>
                    <td style="font-family:monospace; font-size:12px;">{{ $tenant->nik }}</td>
                    <td>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tenant->phone_wa) }}" target="_blank"
                           style="color:#25D366; text-decoration:none;">
                            <i class="bi bi-whatsapp"></i> {{ $tenant->phone_wa }}
                        </a>
                    </td>
                    <td>
                        @if($tenant->room)
                        <a href="{{ route('rooms.show', $tenant->room) }}" style="color:var(--accent-primary); text-decoration:none; font-weight:600;">
                            Kamar {{ $tenant->room->room_number }}
                        </a>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-sm">{{ $tenant->start_date->translatedFormat('d-M-Y') }}</td>
                    <td>
                        @php $depBal = $tenant->deposit_balance; @endphp
                        @if($depBal > 0)
                        <span class="money-text" style="color:#a855f7; font-weight:600; font-size:13px;">Rp {{ number_format($depBal, 0, ',', '.') }}</span>
                        @else
                        <span class="text-muted" style="font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($tenant->status === 'active')
                        <span class="badge badge-success"><i class="bi bi-circle-fill" style="font-size:7px;"></i> Aktif</span>
                        @else
                        <span class="badge badge-secondary">Tidak Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-info btn-sm btn-icon" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-warning btn-sm btn-icon" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('tenants.destroy', $tenant) }}" method="POST"
                                data-confirm="Hapus data penyewa {{ $tenant->name }}?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $tenants->links('components.pagination') }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-people"></i>
        <p>Belum ada data penyewa</p>
        <a href="{{ route('tenants.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Tambah Penyewa</a>
    </div>
    @endif
</div>
@endsection
