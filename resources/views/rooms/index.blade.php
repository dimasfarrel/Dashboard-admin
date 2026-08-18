@extends('layouts.app')
@section('title', 'Database Kamar')
@section('page-title', 'Database Kamar')
@section('page-subtitle', 'Kelola semua kamar kost beserta fasilitas dan penyewa')

@section('topbar-actions')
    <a href="{{ route('system-logs.index', ['menu' => 'Kamar']) }}" class="btn btn-secondary btn-sm" style="background-color: var(--surface-2); color: var(--text-primary); border: 1px solid var(--border-color);">
        <i class="bi bi-clock-history"></i> Log Kamar
    </a>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Kamar</a>
@endsection

@section('content')

{{-- Filter & Stats Bar --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
    <a href="{{ route('rooms.index') }}" class="stat-card" style="--accent-color:#00d4aa; text-decoration:none; transition:all 0.2s; {{ !request()->filled('status') ? 'border-color:var(--accent-primary); background:rgba(255,255,255,0.06);' : '' }}">
        <div class="stat-icon"><i class="bi bi-building"></i></div>
        <div><div class="stat-label">Total Kamar</div><div class="stat-value">{{ \App\Models\Room::count() }}</div></div>
    </a>
    <a href="{{ route('rooms.index', ['status' => 'available']) }}" class="stat-card" style="--accent-color:#00d4aa; text-decoration:none; transition:all 0.2s; {{ request('status') === 'available' ? 'border-color:var(--accent-primary); background:rgba(0,212,170,0.06);' : '' }}">
        <div class="stat-icon" style="background:rgba(0,212,170,0.12);color:#00d4aa;"><i class="bi bi-check-circle"></i></div>
        <div><div class="stat-label">Tersedia</div>
        <div class="stat-value">{{ \App\Models\Room::where('status','available')->count() }}</div></div>
    </a>
    <a href="{{ route('rooms.index', ['status' => 'occupied']) }}" class="stat-card" style="--accent-color:#ef4444; text-decoration:none; transition:all 0.2s; {{ request('status') === 'occupied' ? 'border-color:var(--accent-red); background:rgba(239,68,68,0.06);' : '' }}">
        <div class="stat-icon" style="background:rgba(239,68,68,0.12);color:#ef4444;"><i class="bi bi-person-fill"></i></div>
        <div><div class="stat-label">Dihuni</div>
        <div class="stat-value">{{ \App\Models\Room::where('status','occupied')->count() }}</div></div>
    </a>
    <a href="{{ route('rooms.index', ['status' => 'maintenance']) }}" class="stat-card" style="--accent-color:#eab308; text-decoration:none; transition:all 0.2s; {{ request('status') === 'maintenance' ? 'border-color:var(--accent-yellow); background:rgba(234,179,8,0.06);' : '' }}">
        <div class="stat-icon" style="background:rgba(234,179,8,0.12);color:#eab308;"><i class="bi bi-wrench"></i></div>
        <div><div class="stat-label">Maintenance</div>
        <div class="stat-value">{{ \App\Models\Room::where('status','maintenance')->count() }}</div></div>
    </a>
</div>

{{-- Quick-status change modal --}}
<div id="statusModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div class="card" style="width:340px; margin:0;">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-lightning-charge"></i> Ubah Status Kamar</div>
            <button onclick="closeStatusModal()" class="btn btn-secondary btn-sm btn-icon"><i class="bi bi-x-lg"></i></button>
        </div>
        <div style="padding:4px 0;">
            <p class="text-muted text-sm" style="margin-bottom:14px;" id="statusModalDesc"></p>
            <form id="statusForm" method="POST">
                @csrf @method('PATCH')
                <input type="hidden" name="status" id="statusInput">
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <button type="button" onclick="setStatus('available')" class="btn btn-success" style="justify-content:center;">
                        <span class="room-status-dot available"></span> Tersedia
                    </button>
                    <button type="button" onclick="setStatus('occupied')" class="btn btn-danger" style="justify-content:center;">
                        <span class="room-status-dot occupied"></span> Dihuni
                    </button>
                    <button type="button" onclick="setStatus('maintenance')" class="btn btn-warning" style="justify-content:center;">
                        <span class="room-status-dot maintenance"></span> Maintenance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Room Grid — scrollable, grouped by floor --}}
@if($rooms->count())

@foreach($roomsByFloor->sortKeys() as $floor => $floorRooms)
<div class="card" style="margin-bottom:20px; padding:0; overflow:hidden;">
    {{-- Floor header --}}
    <div style="background:linear-gradient(135deg, rgba(0,212,170,0.12), rgba(59,130,246,0.08)); border-bottom:1px solid var(--border-color); padding:14px 20px; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:36px; height:36px; border-radius:10px; background:rgba(0,212,170,0.15); border:1px solid rgba(0,212,170,0.3); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; color:var(--accent-primary);">
                {{ $floor }}
            </div>
            <div>
                <div style="font-weight:700; font-size:16px; color:var(--text-primary);">Lantai {{ $floor }}</div>
                <div class="text-muted text-sm">{{ $floorRooms->count() }} kamar</div>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <span class="badge badge-success"><span class="room-status-dot available"></span>{{ $floorRooms->where('status','available')->count() }}</span>
            <span class="badge badge-danger"><span class="room-status-dot occupied"></span>{{ $floorRooms->where('status','occupied')->count() }}</span>
            @if($floorRooms->where('status','maintenance')->count() > 0)
            <span class="badge badge-warning">{{ $floorRooms->where('status','maintenance')->count() }} maint</span>
            @endif
        </div>
    </div>

    {{-- Rooms grid on this floor --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:0; padding:16px; gap:14px;">
        @foreach($floorRooms->sortBy('room_number') as $room)
        @php $badge = $room->status_badge; @endphp
        <div class="card" style="padding:0; overflow:hidden; transition:all 0.25s; border-color:{{ $room->status === 'available' ? 'rgba(0,212,170,0.2)' : ($room->status === 'occupied' ? 'rgba(239,68,68,0.2)' : 'rgba(234,179,8,0.2)') }};">
            {{-- Top accent --}}
            <div style="height:3px; background:{{ $room->status === 'available' ? 'var(--accent-primary)' : ($room->status === 'occupied' ? 'var(--accent-red)' : 'var(--accent-yellow)') }};"></div>

            <div style="padding:16px;">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <div style="font-size:18px; font-weight:800; color:var(--text-primary);">
                            Kamar {{ $room->room_number }}
                        </div>
                        <div class="text-muted text-sm">
                            @if($room->type) {{ ucfirst($room->type) }}@endif
                            @if($room->size_sqm) &nbsp;· {{ $room->size_sqm }}m²@endif
                        </div>
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
                        <span class="badge {{ $badge['class'] }}">
                            <span class="room-status-dot {{ $room->status }}"></span>{{ $badge['label'] }}
                        </span>
                        {{-- Quick status button --}}
                        <button class="btn btn-secondary btn-sm btn-icon" onclick="openStatusModal({{ $room->id }}, 'Kamar {{ $room->room_number }}')"
                            title="Ubah status cepat" style="padding:3px 7px; font-size:11px;">
                            <i class="bi bi-lightning-charge"></i>
                        </button>
                    </div>
                </div>

                {{-- Price --}}
                <div style="font-size:16px; font-weight:700; color:var(--accent-primary); margin-bottom:10px;" class="money-text">
                    Rp {{ number_format($room->price, 0, ',', '.') }}<span style="font-size:11px; font-weight:400; color:var(--text-muted);">/bln</span>
                </div>

                {{-- Tenant or Active Lodging Guest --}}
                @if($room->tenant)
                <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.15); border-radius:8px; padding:8px 10px; margin-bottom:10px;">
                    <div style="font-size:10px; color:var(--accent-red); font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">Penyewa Aktif</div>
                    <div style="font-weight:600; color:var(--text-primary); font-size:13px;">{{ $room->tenant->name }}</div>
                    @if($room->tenant->nickname)
                    <div class="text-muted text-sm">"{{ $room->tenant->nickname }}"</div>
                    @endif
                    <div class="text-muted text-sm"><i class="bi bi-whatsapp" style="color:#25D366;"></i> {{ $room->tenant->phone_wa }}</div>
                </div>
                @elseif($room->activeLodging)
                <div style="background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.15); border-radius:8px; padding:8px 10px; margin-bottom:10px;">
                    <div style="font-size:10px; color:#3b82f6; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">Tamu Penginapan</div>
                    <div style="font-weight:600; color:var(--text-primary); font-size:13px;">{{ $room->activeLodging->pic_name }}</div>
                    <div class="text-muted text-sm"><i class="bi bi-whatsapp" style="color:#25D366;"></i> {{ $room->activeLodging->pic_phone }}</div>
                </div>
                @endif

                {{-- Facilities --}}
                @if($room->facilities->count())
                <div style="display:flex; flex-wrap:wrap; gap:4px; margin-bottom:12px;">
                    @foreach($room->facilities->take(4) as $fac)
                    <span style="background:rgba(255,255,255,0.05); border:1px solid var(--border-color); border-radius:4px; padding:2px 6px; font-size:10px; color:var(--text-muted);">
                        <i class="{{ $fac->icon }}"></i> {{ $fac->name }}
                    </span>
                    @endforeach
                    @if($room->facilities->count() > 4)
                    <span style="font-size:10px; color:var(--text-muted); padding:2px 6px;">+{{ $room->facilities->count() - 4 }} lagi</span>
                    @endif
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex gap-2">
                    <a href="{{ route('rooms.show', $room) }}" class="btn btn-info btn-sm flex-1" style="justify-content:center; font-size:12px;">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                    <a href="{{ route('rooms.edit', $room) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('rooms.destroy', $room) }}" method="POST" data-confirm="Yakin hapus Kamar {{ $room->room_number }}?">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

@else
<div class="card">
    <div class="empty-state">
        <i class="bi bi-door-closed"></i>
        <p>Belum ada data kamar. Mulai tambahkan kamar pertama Anda!</p>
        <a href="{{ route('rooms.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Kamar Pertama</a>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
function openStatusModal(roomId, roomLabel) {
    const modal = document.getElementById('statusModal');
    document.getElementById('statusModalDesc').textContent = 'Pilih status baru untuk ' + roomLabel;
    document.getElementById('statusForm').action = '/rooms/' + roomId + '/status';
    modal.style.display = 'flex';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function setStatus(status) {
    document.getElementById('statusInput').value = status;
    document.getElementById('statusForm').submit();
}

// Close on backdrop click
document.getElementById('statusModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatusModal();
});
</script>
@endpush
