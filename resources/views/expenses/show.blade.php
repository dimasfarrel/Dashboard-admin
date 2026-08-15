@extends('layouts.app')
@section('title', 'Detail Pengeluaran')
@section('page-title', 'Detail Pengeluaran')
@section('topbar-actions')
    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<div style="display:grid; grid-template-columns:1fr 280px; gap:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="{{ $expense->category_icon }}"></i> {{ $expense->title }}</div>
            <span class="badge badge-secondary" style="font-size:13px;">{{ $expense->category_label }}</span>
        </div>
        <div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Nominal</span><span class="detail-value money-text" style="font-size:24px; color:var(--accent-red);">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span></div>
            <div class="detail-item"><span class="detail-label">Tanggal</span><span class="detail-value">{{ $expense->expense_date->translatedFormat('d-M-Y') }}</span></div>
            <div class="detail-item"><span class="detail-label">Periode</span><span class="detail-value fw-600">{{ \Carbon\Carbon::now()->setMonth((int)($expense->period_month))->translatedFormat('F') }} {{ $expense->period_year }}</span></div>
            @if($expense->description)
            <div class="detail-item span-full"><span class="detail-label">Deskripsi</span><span class="detail-value">{{ $expense->description }}</span></div>
            @endif
            @if($expense->notes)
            <div class="detail-item span-full"><span class="detail-label">Catatan</span><span class="detail-value">{{ $expense->notes }}</span></div>
            @endif
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:16px;">
        @if($expense->receipt_photo)
        <div class="card">
            <div class="card-title mb-4"><i class="bi bi-receipt"></i> Bukti/Nota</div>
            <a href="{{ asset('storage/' . $expense->receipt_photo) }}" target="_blank">
                <img src="{{ asset('storage/' . $expense->receipt_photo) }}" style="width:100%; border-radius:8px;">
            </a>
        </div>
        @endif
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-warning" style="justify-content:center;"><i class="bi bi-pencil"></i> Edit</a>
                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" data-confirm="Hapus pengeluaran ini?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;"><i class="bi bi-trash"></i> Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
