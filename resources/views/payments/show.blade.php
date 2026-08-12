@extends('layouts.app')
@section('title', 'Detail Pembayaran')
@section('page-title', 'Detail Pembayaran')
@section('topbar-actions')
    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<div style="display:grid; grid-template-columns:1fr 280px; gap:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-receipt"></i> Info Pembayaran</div>
            @if($payment->status === 'paid')     <span class="badge badge-success" style="font-size:14px; padding:6px 16px;">✅ Lunas</span>
            @elseif($payment->status === 'overdue') <span class="badge badge-danger" style="font-size:14px; padding:6px 16px;">🔴 Terlambat</span>
            @else                                   <span class="badge badge-warning" style="font-size:14px; padding:6px 16px;">⏳ Pending</span>
            @endif
        </div>
        <div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Penyewa</span><span class="detail-value" style="font-size:18px;">{{ $payment->tenant?->name ?? '—' }}</span></div>
            <div class="detail-item"><span class="detail-label">Kamar</span><span class="detail-value">Kamar {{ $payment->room?->room_number ?? '—' }}</span></div>
            <div class="detail-item"><span class="detail-label">Periode</span><span class="detail-value" style="font-size:18px; font-weight:700;">{{ $payment->period_label }}</span></div>
            <div class="detail-item"><span class="detail-label">Nominal</span><span class="detail-value money-text" style="font-size:22px; color:var(--accent-primary);">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span></div>
            <div class="detail-item"><span class="detail-label">Metode Pembayaran</span><span class="detail-value">{{ ucfirst($payment->payment_method ?? '—') }}</span></div>
            <div class="detail-item"><span class="detail-label">Jatuh Tempo</span><span class="detail-value {{ $payment->due_date && $payment->due_date->isPast() && $payment->status !== 'paid' ? 'text-danger' : '' }}">{{ $payment->due_date?->format('d F Y') ?? '—' }}</span></div>
            <div class="detail-item"><span class="detail-label">Tanggal Dibayar</span><span class="detail-value">{{ $payment->paid_at?->format('d F Y') ?? '—' }}</span></div>
            @if($payment->notes)
            <div class="detail-item span-full"><span class="detail-label">Catatan</span><span class="detail-value">{{ $payment->notes }}</span></div>
            @endif
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:16px;">
        @if($payment->receipt_photo)
        <div class="card">
            <div class="card-title mb-4"><i class="bi bi-receipt"></i> Bukti Pembayaran</div>
            <a href="{{ asset('storage/' . $payment->receipt_photo) }}" target="_blank">
                <img src="{{ asset('storage/' . $payment->receipt_photo) }}" style="width:100%; border-radius:8px;">
            </a>
        </div>
        @endif
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-warning" style="justify-content:center;"><i class="bi bi-pencil"></i> Edit</a>
                <form action="{{ route('payments.destroy', $payment) }}" method="POST" data-confirm="Hapus data pembayaran ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;"><i class="bi bi-trash"></i> Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
