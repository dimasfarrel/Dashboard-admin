@extends('layouts.app')
@section('title', 'Detail Pendapatan')
@section('page-title', 'Detail Pendapatan Lain-lain')
@section('topbar-actions')
    <div class="flex gap-2">
        <a href="{{ route('other-incomes.edit', $otherIncome) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i> Edit</a>
        <form action="{{ route('other-incomes.destroy', $otherIncome) }}" method="POST" data-confirm="Hapus data pendapatan ini?">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
        </form>
        <a href="{{ route('other-incomes.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
@endsection
@section('content')
<div style="max-width:600px; margin:0 auto;">
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-cash-coin"></i> {{ $otherIncome->title }}</div>
        <span class="badge badge-success">{{ $otherIncome->category_label }}</span>
    </div>
    <div style="display:flex; flex-direction:column; gap:16px;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <div class="text-muted text-sm">Nominal</div>
                <div class="money-text fw-700" style="font-size:24px; color:var(--accent-primary);">Rp {{ number_format($otherIncome->amount, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-muted text-sm">Kategori</div>
                <div><i class="{{ $otherIncome->category_icon }}"></i> {{ $otherIncome->category_label }}</div>
            </div>
            <div>
                <div class="text-muted text-sm">Tanggal</div>
                <div>{{ $otherIncome->income_date->translatedFormat('d F Y') }}</div>
            </div>
            <div>
                <div class="text-muted text-sm">Periode</div>
                <div>{{ \Carbon\Carbon::now()->setMonth((int)($otherIncome->period_month))->translatedFormat('F') }} {{ $otherIncome->period_year }}</div>
            </div>
        </div>

        @if($otherIncome->notes)
        <div>
            <div class="text-muted text-sm" style="margin-bottom:4px;">Catatan</div>
            <div style="background:rgba(255,255,255,0.03); border:1px solid var(--border-color); border-radius:8px; padding:10px 12px;">
                {{ $otherIncome->notes }}
            </div>
        </div>
        @endif

        @if($otherIncome->receipt_photo)
        <div>
            <div class="text-muted text-sm" style="margin-bottom:6px;">Bukti</div>
            <img src="{{ asset('storage/' . $otherIncome->receipt_photo) }}" style="width:100%; max-height:300px; object-fit:contain; border-radius:10px; border:1px solid var(--border-color);">
        </div>
        @endif
    </div>
</div>
</div>
@endsection
