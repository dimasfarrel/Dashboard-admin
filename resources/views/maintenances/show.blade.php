@extends('layouts.app')
@section('title', 'Detail Maintenance')
@section('page-title', 'Detail Maintenance — ' . $maintenance->item_name)
@section('topbar-actions')
    <a href="{{ route('maintenances.edit', $maintenance) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('maintenances.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<div style="display:grid; grid-template-columns:1fr 280px; gap:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-tools"></i> Info Maintenance</div>
            @if($maintenance->status === 'done')        <span class="badge badge-success" style="font-size:14px; padding:6px 16px;">✅ Selesai</span>
            @elseif($maintenance->status === 'in_progress') <span class="badge badge-info" style="font-size:14px; padding:6px 16px;">🔵 Proses</span>
            @elseif($maintenance->status === 'pending')     <span class="badge badge-warning" style="font-size:14px; padding:6px 16px;">⏳ Pending</span>
            @else                                           <span class="badge badge-secondary" style="font-size:14px; padding:6px 16px;">Batal</span>
            @endif
        </div>
        <div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Kamar</span><a href="{{ route('rooms.show', $maintenance->room) }}" style="color:var(--accent-primary); font-weight:700; font-size:18px; text-decoration:none;">Kamar {{ $maintenance->room->room_number }}</a></div>
            <div class="detail-item"><span class="detail-label">Kategori</span><span class="badge badge-secondary" style="font-size:13px;">{{ $maintenance->category_label }}</span></div>
            <div class="detail-item"><span class="detail-label">Nama Item</span><span class="detail-value" style="font-size:18px;">{{ $maintenance->item_name }}</span></div>
            <div class="detail-item"><span class="detail-label">Biaya</span><span class="detail-value money-text" style="font-size:20px; color:var(--accent-red);">Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</span></div>
            <div class="detail-item span-full"><span class="detail-label">Deskripsi Kerusakan</span><span class="detail-value">{{ $maintenance->description }}</span></div>
            <div class="detail-item"><span class="detail-label">Tanggal Laporan</span><span class="detail-value">{{ $maintenance->report_date->format('d F Y') }}</span></div>
            <div class="detail-item"><span class="detail-label">Tanggal Selesai</span><span class="detail-value">{{ $maintenance->done_date?->format('d F Y') ?? '—' }}</span></div>
            <div class="detail-item"><span class="detail-label">Vendor/Tukang</span><span class="detail-value">{{ $maintenance->vendor ?? '—' }}</span></div>
            <div class="detail-item"><span class="detail-label">No HP Vendor</span>
                @if($maintenance->vendor_phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $maintenance->vendor_phone) }}" style="color:#25D366; text-decoration:none;">
                    <i class="bi bi-whatsapp"></i> {{ $maintenance->vendor_phone }}
                </a>
                @else <span class="detail-value">—</span> @endif
            </div>
            @if($maintenance->notes)
            <div class="detail-item span-full"><span class="detail-label">Catatan</span><span class="detail-value">{{ $maintenance->notes }}</span></div>
            @endif
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:16px;">
        @if($maintenance->before_photo || $maintenance->after_photo)
        <div class="card">
            <div class="card-title mb-4"><i class="bi bi-images"></i> Foto</div>
            @if($maintenance->before_photo)
            <div style="margin-bottom:12px;">
                <div class="detail-label" style="margin-bottom:6px;">📸 Before</div>
                <a href="{{ asset('storage/' . $maintenance->before_photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $maintenance->before_photo) }}" style="width:100%; height:130px; object-fit:cover; border-radius:8px;">
                </a>
            </div>
            @endif
            @if($maintenance->after_photo)
            <div>
                <div class="detail-label" style="margin-bottom:6px;">✅ After</div>
                <a href="{{ asset('storage/' . $maintenance->after_photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $maintenance->after_photo) }}" style="width:100%; height:130px; object-fit:cover; border-radius:8px;">
                </a>
            </div>
            @endif
        </div>
        @endif
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('maintenances.edit', $maintenance) }}" class="btn btn-warning" style="justify-content:center;"><i class="bi bi-pencil"></i> Edit</a>
                <form action="{{ route('maintenances.destroy', $maintenance) }}" method="POST" data-confirm="Hapus data maintenance ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;"><i class="bi bi-trash"></i> Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
