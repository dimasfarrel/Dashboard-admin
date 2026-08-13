@extends('layouts.app')
@section('title', 'Detail Penginapan')
@section('page-title', 'Detail Penginapan')
@section('topbar-actions')
    <a href="{{ route('lodgings.edit', $lodging) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('lodgings.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<div style="display:grid; grid-template-columns:1fr 280px; gap:20px;">
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-moon-stars"></i> Info Penginapan</div>
                @if($lodging->status === 'active')    <span class="badge badge-success" style="font-size:14px; padding:6px 16px;">🟢 Aktif</span>
                @elseif($lodging->status === 'completed') <span class="badge badge-secondary" style="font-size:14px; padding:6px 16px;">✅ Selesai</span>
                @else                                     <span class="badge badge-danger" style="font-size:14px; padding:6px 16px;">❌ Batal</span>
                @endif
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Kamar</span>
                    <a href="{{ route('rooms.show', $lodging->room) }}" style="color:var(--accent-primary); font-weight:700; font-size:20px; text-decoration:none;">Kamar {{ $lodging->room->room_number }}</a>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jumlah Tamu</span>
                    <span class="detail-value" style="font-size:20px;"><i class="bi bi-people" style="color:var(--accent-primary);"></i> {{ $lodging->guest_count }} orang</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Check In</span>
                    <span class="detail-value">{{ $lodging->check_in->format('d F Y, H:i') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Check Out</span>
                    <span class="detail-value">{{ $lodging->check_out->format('d F Y, H:i') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Durasi</span>
                    <span class="detail-value" style="font-size:20px; color:var(--accent-primary);">{{ $lodging->duration_days }} Hari</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Harga/Malam</span>
                    <span class="detail-value money-text">Rp {{ number_format($lodging->price_per_night, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Total Harga</span>
                    <span class="detail-value money-text" style="font-size:22px; color:var(--accent-primary);">Rp {{ number_format($lodging->total_price, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Deposit</span>
                    <span class="detail-value money-text">Rp {{ number_format($lodging->deposit, 0, ',', '.') }}</span>
                </div>
                @if($lodging->discount > 0)
                <div class="detail-item">
                    <span class="detail-label">Diskon</span>
                    <span class="detail-value money-text" style="color:var(--accent-red);">Rp {{ number_format($lodging->discount, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($lodging->custom_adjustment != 0)
                <div class="detail-item">
                    <span class="detail-label">Korting Lainnya</span>
                    <span class="detail-value money-text" style="color:var(--accent-red);">Rp {{ number_format($lodging->custom_adjustment, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($lodging->guest_names)
                <div class="detail-item span-full">
                    <span class="detail-label">Nama Tamu</span>
                    <span class="detail-value">{{ $lodging->guest_names }}</span>
                </div>
                @endif
                @if($lodging->notes)
                <div class="detail-item span-full">
                    <span class="detail-label">Catatan</span>
                    <span class="detail-value">{{ $lodging->notes }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-person-vcard"></i> Penanggung Jawab (PIC)</div></div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama PIC</span>
                    <span class="detail-value" style="font-size:18px;">{{ $lodging->pic_name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">No HP / WhatsApp</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lodging->pic_phone) }}" target="_blank"
                       style="color:#25D366; font-weight:600; text-decoration:none; font-size:15px;">
                        <i class="bi bi-whatsapp"></i> {{ $lodging->pic_phone }}
                    </a>
                </div>
                @if($lodging->pic_nik)
                <div class="detail-item">
                    <span class="detail-label">NIK PIC</span>
                    <span class="detail-value" style="font-family:monospace; letter-spacing:1px;">{{ $lodging->pic_nik }}</span>
                </div>
                @endif
                @if($lodging->pic_address)
                <div class="detail-item">
                    <span class="detail-label">Alamat</span>
                    <span class="detail-value">{{ $lodging->pic_address }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card" style="border-color:{{ $lodging->payment_status === 'paid' ? 'rgba(0,212,170,0.25)' : 'rgba(239,68,68,0.25)' }}">
            <div class="card-title mb-4"><i class="bi bi-cash-coin"></i> Status Pembayaran</div>
            @if($lodging->payment_status === 'paid')
            <span class="badge badge-success" style="font-size:14px; padding:8px 20px;">✅ Lunas</span>
            @elseif($lodging->payment_status === 'partial')
            <span class="badge badge-warning" style="font-size:14px; padding:8px 20px;">⚠️ Dibayar Sebagian</span>
            @else
            <span class="badge badge-danger" style="font-size:14px; padding:8px 20px;">❌ Belum Dibayar</span>
            @endif
            @if($lodging->paid_at)
            <div style="margin-top:10px; font-size:13px; color:var(--text-muted);">
                Tgl Bayar: {{ $lodging->paid_at->format('d M Y') }}
            </div>
            @endif
            <div style="margin-top:16px; font-size:24px; font-weight:800; color:var(--accent-primary);" class="money-text">
                Rp {{ number_format($lodging->total_price, 0, ',', '.') }}
            </div>
        </div>

        <div class="card">
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('lodgings.edit', $lodging) }}" class="btn btn-warning" style="justify-content:center;"><i class="bi bi-pencil"></i> Edit</a>
                <form action="{{ route('lodgings.destroy', $lodging) }}" method="POST" data-confirm="Hapus data penginapan ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;"><i class="bi bi-trash"></i> Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
