@extends('layouts.app')
@section('title', 'Detail Penyewa — ' . $tenant->name)
@section('page-title', $tenant->name)
@section('page-subtitle', 'Detail data penyewa dan riwayat pembayaran')

@section('topbar-actions')
    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <a href="{{ route('tenants.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('tenants.index') }}">Penyewa</a>
    <span class="separator">/</span>
    <span class="current">{{ $tenant->name }}</span>
</div>

<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Data Diri --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-person-badge"></i> Data Diri</div>
                @if($tenant->status === 'active')
                <span class="badge badge-success"><i class="bi bi-circle-fill" style="font-size:7px;"></i> Aktif</span>
                @else
                <span class="badge badge-secondary">Tidak Aktif</span>
                @endif
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama Lengkap</span>
                    <span class="detail-value">
                        {{ $tenant->name }}
                        @if($tenant->nickname) <span style="font-weight:400; color:var(--text-secondary); font-size:13px;">("{{ $tenant->nickname }}")</span> @endif
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nomor Identitas</span>
                    <span class="detail-value" style="font-family:monospace; letter-spacing:1px;">{{ $tenant->nik }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">WhatsApp</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tenant->phone_wa) }}" target="_blank"
                       style="color:#25D366; text-decoration:none; font-weight:600; font-size:15px;">
                        <i class="bi bi-whatsapp"></i> {{ $tenant->phone_wa }}
                    </a>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jenis Kelamin</span>
                    <span class="detail-value">{{ $tenant->gender ? ucfirst($tenant->gender) : '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Lahir</span>
                    <span class="detail-value">{{ $tenant->birth_date?->format('d F Y') ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Pekerjaan</span>
                    <span class="detail-value">{{ $tenant->occupation ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Alamat Lengkap</span>
                    <span class="detail-value">{{ $tenant->origin_city ?: '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Kamar</span>
                    @if($tenant->room)
                    <a href="{{ route('rooms.show', $tenant->room) }}" style="color:var(--accent-primary); text-decoration:none; font-weight:600;">
                        <i class="bi bi-door-open"></i> Kamar {{ $tenant->room->room_number }}
                    </a>
                    @else
                    <span class="detail-value">—</span>
                    @endif
                </div>
                <div class="detail-item">
                    <span class="detail-label">Mulai Sewa</span>
                    <span class="detail-value">{{ $tenant->start_date->format('d F Y') }}</span>
                </div>
                @if($tenant->end_date)
                <div class="detail-item">
                    <span class="detail-label">Kontrak Berakhir</span>
                    <span class="detail-value {{ $tenant->end_date->isPast() ? 'text-danger' : '' }}">
                        {{ $tenant->end_date->format('d F Y') }}
                        @if($tenant->end_date->isPast())
                        <span class="badge badge-danger" style="margin-left:6px;">Kadaluarsa</span>
                        @endif
                    </span>
                </div>
                @endif
            </div>

            @if($customFields->count())
            <hr class="section-divider">
            <div class="card-title mb-4" style="font-size:13px;"><i class="bi bi-sliders" style="color:var(--accent-primary);"></i> Informasi Tambahan</div>
            <div class="detail-grid">
                @foreach($customFields as $field)
                <div class="detail-item {{ $field->type === 'textarea' ? 'span-full' : '' }}">
                    <span class="detail-label">{{ $field->name }}</span>
                    <span class="detail-value">{{ $tenant->getCustomFieldValue($field->field_key) ?: '—' }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @if($tenant->emergency_contact_name || $tenant->emergency_contact_phone)
            <hr class="section-divider">
            <div class="card-title mb-4" style="font-size:13px;"><i class="bi bi-telephone-fill" style="color:var(--accent-orange);"></i> Kontak Darurat</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama</span>
                    <span class="detail-value">{{ $tenant->emergency_contact_name ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nomor HP</span>
                    @if($tenant->emergency_contact_phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tenant->emergency_contact_phone) }}" target="_blank"
                       style="color:#25D366; text-decoration:none;">
                        <i class="bi bi-whatsapp"></i> {{ $tenant->emergency_contact_phone }}
                    </a>
                    @else
                    <span class="detail-value">—</span>
                    @endif
                </div>
            </div>
            @endif

            @if($tenant->notes)
            <hr class="section-divider">
            <div class="detail-label" style="margin-bottom:6px;">Catatan</div>
            <div style="color:var(--text-secondary);">{{ $tenant->notes }}</div>
            @endif
        </div>

        {{-- Riwayat Pembayaran --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-receipt"></i> Riwayat Pembayaran</div>
                <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">+ Catat</a>
            </div>
            @if($tenant->payments->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Periode</th><th>Nominal</th><th>Metode</th><th>Dibayar</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($tenant->payments as $pay)
                        <tr>
                            <td class="fw-600">{{ $pay->period_label }}</td>
                            <td class="money-text">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($pay->payment_method ?? '—') }}</td>
                            <td class="text-sm">{{ $pay->paid_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if($pay->status === 'paid')     <span class="badge badge-success">Lunas</span>
                                @elseif($pay->status === 'overdue') <span class="badge badge-danger">Terlambat</span>
                                @else                               <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('payments.edit', $pay) }}" class="btn btn-secondary btn-sm btn-icon">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding-top:16px; border-top:1px solid var(--border-color); margin-top:16px;">
                <span class="text-muted text-sm">Total dibayar: </span>
                <span class="fw-700 money-text" style="color:var(--accent-primary);">
                    Rp {{ number_format($tenant->payments->where('status','paid')->sum('amount'), 0, ',', '.') }}
                </span>
            </div>
            @else
            <div class="text-muted text-sm">Belum ada riwayat pembayaran.</div>
            @endif
        </div>
    </div>

    {{-- RIGHT: Dokumen --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        @if($tenant->ktp_photo || $tenant->selfie_photo)
        <div class="card">
            <div class="card-title mb-4"><i class="bi bi-images"></i> Dokumen</div>
            @if($tenant->ktp_photo)
            <div style="margin-bottom:12px;">
                <div class="detail-label" style="margin-bottom:6px;">📋 Foto KTP</div>
                <a href="{{ asset('storage/' . $tenant->ktp_photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $tenant->ktp_photo) }}" style="width:100%; height:140px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                </a>
            </div>
            @endif
            @if($tenant->selfie_photo)
            <div>
                <div class="detail-label" style="margin-bottom:6px;">🤳 Selfie + KTP</div>
                <a href="{{ asset('storage/' . $tenant->selfie_photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $tenant->selfie_photo) }}" style="width:100%; height:140px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                </a>
            </div>
            @endif
        </div>
        @endif

        <div class="card">
            <div class="card-title mb-4"><i class="bi bi-lightning"></i> Aksi</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-warning" style="justify-content:center;">
                    <i class="bi bi-pencil"></i> Edit Data
                </a>
                <a href="{{ route('payments.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    <i class="bi bi-cash-coin"></i> Catat Pembayaran
                </a>
                <hr class="section-divider">
                <form action="{{ route('tenants.destroy', $tenant) }}" method="POST"
                    data-confirm="Hapus data penyewa {{ $tenant->name }}? Riwayat pembayaran juga akan terhapus.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;">
                        <i class="bi bi-trash"></i> Hapus Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
