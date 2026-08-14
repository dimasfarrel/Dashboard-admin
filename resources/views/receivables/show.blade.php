@extends('layouts.app')
@section('title', 'Detail Piutang')
@section('page-title', 'Detail Piutang')

@section('content')
<div class="flex" style="gap:24px; flex-wrap:wrap;">
    
    {{-- Loan Info --}}
    <div class="card" style="flex:1; min-width:300px;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div class="card-title"><i class="bi bi-info-circle"></i> Informasi Piutang</div>
            <a href="{{ route('receivables.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
        <div style="display:grid; grid-template-columns:120px 1fr; gap:12px 16px; font-size:14px;">
            <div class="text-muted">Nama Peminjam</div>
            <div class="fw-600">{{ $loan->name }}</div>
            
            <div class="text-muted">Tgl Piutang</div>
            <div>{{ $loan->loan_date->format('d/m/Y') }}</div>
            
            <div class="text-muted">Keperluan</div>
            <div>{{ $loan->purpose }}</div>
            
            <div class="text-muted">Keterangan</div>
            <div>{{ $loan->notes ?: '-' }}</div>
            
            <div class="text-muted">Total Piutang</div>
            <div class="money-text text-primary">Rp {{ number_format($loan->total_amount, 0, ',', '.') }}</div>
            
            <div class="text-muted">Telah Dibayar</div>
            <div class="money-text text-success">Rp {{ number_format($loan->paid_amount, 0, ',', '.') }}</div>
            
            <div class="text-muted">Sisa Piutang</div>
            <div class="money-text text-danger" style="font-size:18px;">Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}</div>
            
            <div class="text-muted">Status</div>
            <div>
                @if($loan->is_paid)
                    <span class="badge badge-success">Lunas</span>
                @else
                    <span class="badge badge-warning">Belum Lunas</span>
                @endif
            </div>
        </div>

        <div style="margin-top:20px;">
            @php 
                $percent = $loan->total_amount > 0 ? ($loan->paid_amount / $loan->total_amount) * 100 : 0;
                if($percent > 100) $percent = 100;
            @endphp
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                <span class="text-muted">Progress Pembayaran</span>
                <span class="fw-600">{{ number_format($percent, 1) }}%</span>
            </div>
            <div style="width:100%; background:var(--bg-hover); border-radius:100px; height:8px; overflow:hidden;">
                <div style="width:{{ $percent }}%; background:var(--accent-success); height:100%; transition:width 0.5s ease;"></div>
            </div>
        </div>
    </div>

    {{-- Repayments --}}
    <div class="card" style="flex:2; min-width:400px;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div class="card-title"><i class="bi bi-clock-history"></i> Riwayat Pembayaran</div>
            @if(!$loan->is_paid)
            <button class="btn btn-success btn-sm" onclick="openModal('addRepaymentModal')"><i class="bi bi-plus"></i> Catat Pembayaran</button>
            @endif
        </div>
        
        @if($loan->repayments->count())
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nominal</th>
                        <th>Keterangan</th>
                        <th style="width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loan->repayments as $rep)
                    <tr>
                        <td>{{ $rep->repayment_date->format('d/m/Y') }}</td>
                        <td class="money-text text-success">+ Rp {{ number_format($rep->amount, 0, ',', '.') }}</td>
                        <td>{{ $rep->notes ?: '-' }}</td>
                        <td>
                            <form action="{{ route('receivable-repayments.destroy', $rep) }}" method="POST" data-confirm="Hapus riwayat pembayaran ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <i class="bi bi-cash-coin"></i>
            <p>Belum ada riwayat pembayaran untuk piutang ini.</p>
        </div>
        @endif
    </div>
</div>

{{-- Add Repayment Modal --}}
@if(!$loan->is_paid)
<div id="addRepaymentModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Catat Pembayaran Masuk</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addRepaymentModal')">✕</button>
        </div>
        <form action="{{ route('receivables.repayments.store', $loan) }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Tanggal Pembayaran <span class="required">*</span></label>
                <input type="date" name="repayment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group mb-4">
                <label>Nominal (Rp) <span class="required">*</span></label>
                <input type="number" name="amount" class="form-control" value="{{ $loan->remaining_amount > 0 ? $loan->remaining_amount : '' }}" required>
                <span class="form-hint">Sisa tagihan: Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}</span>
            </div>
            <div class="form-group mb-6">
                <label>Keterangan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addRepaymentModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
.modal-backdrop {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(4, 6, 12, 0.7); backdrop-filter: blur(12px);
    z-index: 1000; display: flex; align-items: center; justify-content: center;
}
.modal-card {
    background: var(--bg-card); border: 1px solid var(--border-accent); border-radius: var(--radius-lg);
    width: 100%; max-width: 500px; padding: 24px; box-shadow: var(--shadow-lg);
    animation: zoomIn 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes zoomIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
</style>
@endpush

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.remove('d-none'); }
function closeModal(id) { document.getElementById(id).classList.add('d-none'); }
</script>
@endpush
