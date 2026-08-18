@extends('layouts.app')
@section('title', 'Catat Pembayaran')
@section('page-title', 'Catat Pembayaran Sewa')

@section('topbar-actions')
    <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection

@section('content')
<form action="{{ route('payments.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-cash-coin"></i> Detail Pembayaran</div></div>
        <div class="form-grid">
            <div class="form-group span-full">
                <label>Penyewa <span class="required">*</span></label>
                <select name="tenant_id" id="tenant_select" class="form-control @error('tenant_id') is-invalid @enderror" required onchange="fillRoom(this)">
                    <option value="">— Pilih Penyewa —</option>
                    @php
                        $activeTenants = $tenants->where('status', 'active');
                        $inactiveTenants = $tenants->where('status', 'inactive');
                    @endphp
                    @if($activeTenants->count())
                    <optgroup label="Penyewa Aktif">
                        @foreach($activeTenants as $t)
                        <option value="{{ $t->id }}" data-room="{{ $t->room_id }}" data-price="{{ $t->room?->price }}" {{ old('tenant_id', request('tenant_id')) == $t->id ? 'selected' : '' }}>
                            Kamar {{ $t->room?->room_number }} - {{ $t->name }}{{ $t->nickname ? ' - ' . $t->nickname : '' }} (Rp {{ number_format($t->room?->price, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </optgroup>
                    @endif
                    @if($inactiveTenants->count())
                    <optgroup label="Penyewa Non-Aktif (Riwayat)">
                        @foreach($inactiveTenants as $t)
                        <option value="{{ $t->id }}" data-room="{{ $t->room_id }}" data-price="{{ $t->room?->price }}" {{ old('tenant_id', request('tenant_id')) == $t->id ? 'selected' : '' }}>
                            Kamar {{ $t->room?->room_number }} - {{ $t->name }}{{ $t->nickname ? ' - ' . $t->nickname : '' }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endif
                </select>
                @error('tenant_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <input type="hidden" name="room_id" id="room_id" value="{{ old('room_id') }}">

            <div class="form-group">
                <label>Periode Bulan <span class="required">*</span></label>
                <select name="period_month" class="form-control" required>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ old('period_month', now()->month) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tahun <span class="required">*</span></label>
                <input type="number" name="period_year" class="form-control" value="{{ old('period_year', now()->year) }}" min="2020" required>
            </div>
            <div class="form-group">
                <label>Mode Input Nominal</label>
                <div class="flex gap-4" style="margin-top: 6px; margin-bottom: 4px;">
                    <label class="flex items-center gap-2" style="cursor:pointer; font-weight:normal; text-transform:none; font-size:13px; color:var(--text-secondary);">
                        <input type="radio" name="amount_mode" value="auto" checked onchange="toggleAmountMode(this.value)"> 
                        Otomatis (Harga Kamar)
                    </label>
                    <label class="flex items-center gap-2" style="cursor:pointer; font-weight:normal; text-transform:none; font-size:13px; color:var(--text-secondary);">
                        <input type="radio" name="amount_mode" value="manual" onchange="toggleAmountMode(this.value)"> 
                        Ketik Manual
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Nominal Pembayaran <span class="required">*</span></label>
                <input type="text" inputmode="numeric" name="amount" id="amount_input" class="form-control input-rupiah @error('amount') is-invalid @enderror"
                    value="{{ old('amount') }}" placeholder="Rp" min="0" required readonly>
                @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select name="payment_method" class="form-control">
                    <option value="">— Pilih —</option>
                    <option value="tunai"    {{ old('payment_method') == 'tunai'    ? 'selected' : '' }}>💵 Tunai</option>
                    <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>🏦 Transfer</option>
                    <option value="qris"     {{ old('payment_method') == 'qris'     ? 'selected' : '' }}>📱 QRIS</option>
                    <option value="lain-lain"{{ old('payment_method') == 'lain-lain'? 'selected' : '' }}>Lain-lain</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status Pembayaran <span class="required">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="paid"    {{ old('status', 'paid') == 'paid'    ? 'selected' : '' }}>✅ Lunas</option>
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="overdue" {{ old('status') == 'overdue' ? 'selected' : '' }}>🔴 Terlambat</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Dibayar</label>
                <input type="date" name="paid_at" class="form-control" value="{{ old('paid_at', date('Y-m-d')) }}">
            </div>

            <div class="form-group span-full">
                <label>Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-image"></i> Bukti Bayar</div></div>
            <div class="photo-upload-area" onclick="document.getElementById('receipt').click()">
                <i class="bi bi-receipt" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                <div style="font-size:13px;">Upload Bukti Transfer/Nota</div>
            </div>
            <input type="file" id="receipt" name="receipt_photo" accept="image/*" style="display:none;"
                onchange="previewPhoto(this)">
            <img id="receipt-preview" style="display:none; width:100%; height:160px; object-fit:cover; border-radius:8px; margin-top:10px;">
        </div>
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="bi bi-floppy"></i> Simpan Pembayaran
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

function previewPhoto(input) {
    const preview = document.getElementById('receipt-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}

// Init on load
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('tenant_select');
    if (sel.value) {
        fillRoom(sel);
    }
});
</script>
@endpush
