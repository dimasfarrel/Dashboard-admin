@extends('layouts.app')
@section('title', 'Edit Pembayaran')
@section('page-title', 'Edit Pembayaran')
@section('topbar-actions')
    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<form action="{{ route('payments.update', $payment) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-cash-coin"></i> Edit Pembayaran</div></div>
        <div class="form-grid">
            <div class="form-group span-full">
                <label>Penyewa <span class="required">*</span></label>
                <select name="tenant_id" id="tenant_select" class="form-control" required onchange="fillRoom(this)">
                    @php
                        $activeTenants = $tenants->where('status', 'active');
                        $inactiveTenants = $tenants->where('status', 'inactive');
                    @endphp
                    @if($activeTenants->count())
                    <optgroup label="Penyewa Aktif">
                        @foreach($activeTenants as $t)
                        <option value="{{ $t->id }}" data-room="{{ $t->room_id }}" data-price="{{ $t->room?->price }}"
                            {{ old('tenant_id', $payment->tenant_id) == $t->id ? 'selected' : '' }}>
                            Kamar {{ $t->room?->room_number }} - {{ $t->name }}{{ $t->nickname ? ' - ' . $t->nickname : '' }} (Rp {{ number_format($t->room?->price, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </optgroup>
                    @endif
                    @if($inactiveTenants->count())
                    <optgroup label="Penyewa Non-Aktif (Riwayat)">
                        @foreach($inactiveTenants as $t)
                        <option value="{{ $t->id }}" data-room="{{ $t->room_id }}" data-price="{{ $t->room?->price }}"
                            {{ old('tenant_id', $payment->tenant_id) == $t->id ? 'selected' : '' }}>
                            Kamar {{ $t->room?->room_number }} - {{ $t->name }}{{ $t->nickname ? ' - ' . $t->nickname : '' }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endif
                </select>
            </div>
            <input type="hidden" name="room_id" id="room_id" value="{{ old('room_id', $payment->room_id) }}">

            <div class="form-group">
                <label>Periode Bulan <span class="required">*</span></label>
                <select name="period_month" class="form-control" required>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ old('period_month', $payment->period_month) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tahun <span class="required">*</span></label>
                <input type="number" name="period_year" class="form-control" value="{{ old('period_year', $payment->period_year) }}" required>
            </div>
            @php
                $isAuto = $payment->amount == ($payment->room?->price ?? 0);
            @endphp
            <div class="form-group">
                <label>Mode Input Nominal</label>
                <div class="flex gap-4" style="margin-top: 6px; margin-bottom: 4px;">
                    <label class="flex items-center gap-2" style="cursor:pointer; font-weight:normal; text-transform:none; font-size:13px; color:var(--text-secondary);">
                        <input type="radio" name="amount_mode" value="auto" {{ $isAuto ? 'checked' : '' }} onchange="toggleAmountMode(this.value)"> 
                        Otomatis (Harga Kamar)
                    </label>
                    <label class="flex items-center gap-2" style="cursor:pointer; font-weight:normal; text-transform:none; font-size:13px; color:var(--text-secondary);">
                        <input type="radio" name="amount_mode" value="manual" {{ !$isAuto ? 'checked' : '' }} onchange="toggleAmountMode(this.value)"> 
                        Ketik Manual
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Nominal <span class="required">*</span></label>
                <input type="text" inputmode="numeric" name="amount" id="amount_input" class="form-control input-rupiah" value="{{ old('amount', $payment->amount) }}" required {{ $isAuto ? 'readonly' : '' }}>
            </div>
            <div class="form-group">
                <label>Metode</label>
                <select name="payment_method" class="form-control">
                    <option value="">— Pilih —</option>
                    @foreach(['tunai','transfer','qris','lain-lain'] as $m)
                    <option value="{{ $m }}" {{ old('payment_method', $payment->payment_method) == $m ? 'selected' : '' }}>
                        {{ ucfirst($m) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Status <span class="required">*</span></label>
                <select name="status" class="form-control" required>
                    @foreach(['paid' => '✅ Lunas','pending' => '⏳ Pending','overdue' => '🔴 Terlambat'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('status', $payment->status) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Dibayar</label>
                <input type="date" name="paid_at" class="form-control" value="{{ old('paid_at', $payment->paid_at?->format('Y-m-d')) }}">
            </div>

            <div class="form-group span-full">
                <label>Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $payment->notes) }}</textarea>
            </div>
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-image"></i> Bukti Bayar</div></div>
            @if($payment->receipt_photo)
            <img src="{{ asset('storage/' . $payment->receipt_photo) }}" style="width:100%; height:140px; object-fit:cover; border-radius:8px; margin-bottom:10px;">
            @endif
            <input type="file" name="receipt_photo" class="form-control" accept="image/*">
        </div>
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="bi bi-floppy"></i> Simpan Perubahan
                </button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary" style="width:100%; justify-content:center;">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
function fillRoom(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('room_id').value = opt.dataset.room || '';
    
    // Auto fill price if mode is auto
    const activeMode = document.querySelector('input[name="amount_mode"]:checked').value;
    if (activeMode === 'auto') {
        const price = opt.dataset.price || '';
        document.getElementById('amount_input').value = price ? formatRupiah(price) : '';
    }
}

function toggleAmountMode(mode) {
    const amountInput = document.getElementById('amount_input');
    const sel = document.getElementById('tenant_select');
    
    if (mode === 'auto') {
        amountInput.setAttribute('readonly', 'readonly');
        if (sel.value) {
            const opt = sel.options[sel.selectedIndex];
            amountInput.value = opt.dataset.price ? formatRupiah(opt.dataset.price) : '';
        } else {
            amountInput.value = '';
        }
    } else {
        amountInput.removeAttribute('readonly');
    }
}
</script>
@endpush
