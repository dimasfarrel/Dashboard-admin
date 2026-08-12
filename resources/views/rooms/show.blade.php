@extends('layouts.app')
@section('title', 'Detail Kamar ' . $room->room_number)
@section('page-title', 'Kamar ' . $room->room_number)
@section('page-subtitle', 'Detail informasi, fasilitas, penyewa, dan riwayat kamar')

@section('topbar-actions')
    <a href="{{ route('rooms.edit', $room) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil"></i> Edit Kamar
    </a>
    <a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('rooms.index') }}">Kamar</a>
    <span class="separator">/</span>
    <span class="current">Kamar {{ $room->room_number }}</span>
</div>

<div style="display:grid; grid-template-columns:1fr 320px; gap:24px;">

    {{-- LEFT --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Header Card --}}
        <div class="card" style="padding:0; overflow:hidden;">
            @if($room->photo)
            <img src="{{ asset('storage/' . $room->photo) }}" style="width:100%; height:220px; object-fit:cover;">
            @endif
            <div style="padding:24px;">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 style="font-size:28px; font-weight:800;">Kamar {{ $room->room_number }}</h1>
                        <div class="text-muted" style="margin-top:4px;">
                            Lantai {{ $room->floor }}
                            @if($room->type) · {{ ucfirst($room->type) }} @endif
                            @if($room->size_sqm) · {{ $room->size_sqm }} m² @endif
                        </div>
                    </div>
                    @php $badge = $room->status_badge; @endphp
                    <span class="badge {{ $badge['class'] }}" style="font-size:14px; padding:6px 16px;">
                        <span class="room-status-dot {{ $room->status }}"></span>{{ $badge['label'] }}
                    </span>
                </div>

                <div style="font-size:26px; font-weight:800; color:var(--accent-primary);" class="money-text">
                    Rp {{ number_format($room->price, 0, ',', '.') }}
                    <span style="font-size:14px; font-weight:400; color:var(--text-muted);">/ bulan</span>
                </div>

                @if($room->description)
                <p style="margin-top:16px; color:var(--text-secondary); line-height:1.7;">{{ $room->description }}</p>
                @endif
            </div>
        </div>

        {{-- Fasilitas --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-check2-all"></i> Fasilitas Kamar</div>
                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
            @if($room->facilities->count())
            @php $grouped = $room->facilities->groupBy('category'); @endphp
            @foreach($grouped as $cat => $facs)
            <div class="facility-section">
                <div class="facility-section-title">
                    {{ match($cat) {
                        'furnitur' => '🪑 Furnitur',
                        'elektronik' => '⚡ Elektronik',
                        'kamar_mandi' => '🚿 Kamar Mandi',
                        'lainnya' => '✨ Lainnya',
                        default => ucfirst($cat)
                    } }}
                </div>
                <div class="facility-grid">
                    @foreach($facs as $fac)
                    <div class="facility-item checked" style="cursor:default;">
                        <i class="bi bi-check-circle-fill" style="color:var(--accent-primary);"></i>
                        <i class="{{ $fac->icon }}"></i>
                        <span class="facility-label">{{ $fac->name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            @else
            <div class="text-muted text-sm" style="padding:12px 0;">Belum ada fasilitas yang ditambahkan.</div>
            @endif
        </div>

        {{-- Riwayat Pembayaran --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-cash-coin"></i> Riwayat Pembayaran</div>
                <a href="{{ route('payments.create', $room->tenant ? ['tenant_id' => $room->tenant->id] : []) }}" class="btn btn-primary btn-sm">+ Catat</a>
            </div>
            @if($room->payments->count())
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Periode</th><th>Penyewa</th><th>Nominal</th><th>Metode</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($room->payments as $pay)
                        <tr>
                            <td>{{ $pay->period_label }}</td>
                            <td>{{ $pay->tenant?->name ?? '—' }}</td>
                            <td class="money-text">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($pay->payment_method ?? '—') }}</td>
                            <td>
                                @if($pay->status === 'paid')   <span class="badge badge-success">Lunas</span>
                                @elseif($pay->status === 'overdue') <span class="badge badge-danger">Terlambat</span>
                                @else <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-muted text-sm">Belum ada riwayat pembayaran.</div>
            @endif
        </div>

        {{-- Riwayat Maintenance --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-tools"></i> Riwayat Maintenance</div>
                <a href="{{ route('maintenances.create', ['room_id' => $room->id]) }}" class="btn btn-secondary btn-sm">+ Catat</a>
            </div>
            @if($room->maintenances->count())
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Tanggal</th><th>Item</th><th>Kategori</th><th>Biaya</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($room->maintenances as $m)
                        <tr>
                            <td class="text-sm">{{ $m->report_date->format('d/m/Y') }}</td>
                            <td><strong>{{ $m->item_name }}</strong></td>
                            <td>{{ $m->category_label }}</td>
                            <td class="money-text">Rp {{ number_format($m->cost, 0, ',', '.') }}</td>
                            <td>
                                @if($m->status === 'done')        <span class="badge badge-success">Selesai</span>
                                @elseif($m->status === 'in_progress') <span class="badge badge-info">Proses</span>
                                @elseif($m->status === 'pending')     <span class="badge badge-warning">Pending</span>
                                @else                                  <span class="badge badge-secondary">Batal</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-muted text-sm">Belum ada riwayat maintenance.</div>
            @endif
        </div>
    </div>

    {{-- RIGHT --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Penyewa Aktif --}}
        <div class="card" style="border-color:{{ $room->tenant ? 'rgba(239,68,68,0.25)' : 'rgba(0,212,170,0.15)' }}">
            <div class="card-header">
                <div class="card-title" style="color:{{ $room->tenant ? 'var(--accent-red)' : 'var(--accent-primary)' }}">
                    <i class="bi bi-{{ $room->tenant ? 'person-fill' : 'person-plus' }}"></i>
                    Penyewa Aktif
                </div>
            </div>
            @if($room->tenant)
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div class="detail-item">
                    <span class="detail-label">Nama Penyewa</span>
                    <span class="detail-value">{{ $room->tenant->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nomor KTP</span>
                    <span class="detail-value" style="font-family:monospace;">{{ $room->tenant->nik }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">WhatsApp</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $room->tenant->phone_wa) }}" target="_blank"
                       style="color:#25D366; text-decoration:none; font-weight:600;">
                        <i class="bi bi-whatsapp"></i> {{ $room->tenant->phone_wa }}
                    </a>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Mulai Sewa</span>
                    <span class="detail-value">{{ $room->tenant->start_date->format('d F Y') }}</span>
                </div>
                @if($room->tenant->end_date)
                <div class="detail-item">
                    <span class="detail-label">Kontrak Berakhir</span>
                    <span class="detail-value {{ $room->tenant->end_date->isPast() ? 'text-danger' : '' }}">
                        {{ $room->tenant->end_date->format('d F Y') }}
                    </span>
                </div>
                @endif
                <hr class="section-divider">
                <div class="flex gap-2">
                    <a href="{{ route('tenants.show', $room->tenant) }}" class="btn btn-info btn-sm" style="flex:1; justify-content:center;">
                        <i class="bi bi-eye"></i> Detail
                    </a>
                    <a href="{{ route('tenants.edit', $room->tenant) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>
                </div>
            </div>
            @else
            <div class="text-muted text-sm" style="margin-bottom:16px;">
                Kamar ini belum memiliki penyewa aktif.
            </div>
            <a href="{{ route('tenants.create') }}?room_id={{ $room->id }}" class="btn btn-primary" style="width:100%; justify-content:center;">
                <i class="bi bi-person-plus"></i> Tambah Penyewa
            </a>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-title mb-4"><i class="bi bi-lightning"></i> Aksi Cepat</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('payments.create', $room->tenant ? ['tenant_id' => $room->tenant->id] : []) }}" class="btn btn-secondary" style="justify-content:flex-start;">
                    <i class="bi bi-cash-coin"></i> Catat Pembayaran
                </a>
                <a href="{{ route('maintenances.create', ['room_id' => $room->id]) }}" class="btn btn-secondary" style="justify-content:flex-start;">
                    <i class="bi bi-tools"></i> Laporkan Maintenance
                </a>
                <hr class="section-divider">
                <form action="{{ route('rooms.destroy', $room) }}" method="POST"
                    data-confirm="Yakin hapus Kamar {{ $room->room_number }}? Semua data terkait juga akan dihapus!">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;">
                        <i class="bi bi-trash"></i> Hapus Kamar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
