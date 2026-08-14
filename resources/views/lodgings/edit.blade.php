@extends('layouts.app')
@section('title', 'Edit Penginapan')
@section('page-title', 'Edit Penginapan')
@section('topbar-actions')
    <a href="{{ route('lodgings.show', $lodging) }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<form action="{{ route('lodgings.update', $lodging) }}" method="POST">
@csrf @method('PUT')
<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-door-open"></i> Kamar & Durasi</div></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Kamar <span class="required">*</span></label>
                    <select name="room_id" id="room_select" class="form-control" required onchange="onRoomChange(this)">
                        @foreach($rooms as $r)
                        @php $activeLodging = $r->activeLodging; @endphp
                        <option value="{{ $r->id }}"
                                data-tenant-name="{{ $r->tenant ? $r->tenant->name : ($activeLodging ? $activeLodging->pic_name : '') }}"
                                data-tenant-phone="{{ $r->tenant ? $r->tenant->phone_wa : ($activeLodging ? $activeLodging->pic_phone : '') }}"
                                data-tenant-nik="{{ $r->tenant ? $r->tenant->nik : ($activeLodging ? $activeLodging->pic_nik : '') }}"
                                data-tenant-address="{{ $r->tenant ? $r->tenant->origin_city : ($activeLodging ? $activeLodging->pic_address : '') }}"
                                {{ old('room_id', $lodging->room_id) == $r->id ? 'selected' : '' }}>
                            Kamar {{ $r->room_number }}
                            @if($r->tenant) (Dihuni: {{ $r->tenant->name }})
                            @elseif($activeLodging && $activeLodging->id !== $lodging->id) (Tamu: {{ $activeLodging->pic_name }})
                            @elseif($r->status === 'maintenance') (Maintenance)
                            @else (Tersedia) @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Tamu <span class="required">*</span></label>
                    <input type="number" name="guest_count" id="guest_count" class="form-control" value="{{ old('guest_count', $lodging->guest_count) }}" min="1" required onchange="calcTotal()">
                </div>
                <div class="form-group">
                    <label>Check In <span class="required">*</span></label>
                    <input type="datetime-local" name="check_in" id="check_in" class="form-control" value="{{ old('check_in', $lodging->check_in->format('Y-m-d\TH:i')) }}" required onchange="calcDuration()">
                </div>
                <div class="form-group">
                    <label>Check Out <span class="required">*</span></label>
                    <input type="datetime-local" name="check_out" id="check_out" class="form-control" value="{{ old('check_out', $lodging->check_out->format('Y-m-d\TH:i')) }}" required onchange="calcDuration()">
                </div>
                <div class="form-group">
                    <label>Harga per Malam (Rp) <span class="required">*</span></label>
                    <input type="text" inputmode="numeric" name="price_per_night" id="price_per_night" class="form-control input-rupiah" value="{{ old('price_per_night', $lodging->price_per_night) }}" min="0" required onchange="calcTotal()">
                </div>
                <div class="form-group">
                    <label>Diskon Harian (Rp/malam)</label>
                    <input type="text" inputmode="numeric" name="daily_discount" id="daily_discount" class="form-control input-rupiah"
                        value="{{ old('daily_discount', $lodging->daily_discount ?? 0) }}" min="0" onchange="calcTotal()">
                    <span class="form-hint">Potongan per malam, dikalikan jumlah malam</span>
                </div>
                <div class="form-group">
                    <label>Diskon Tetap (Rp)</label>
                    <input type="text" inputmode="numeric" name="fixed_discount" id="fixed_discount" class="form-control input-rupiah"
                        value="{{ old('fixed_discount', $lodging->fixed_discount ?? $lodging->discount ?? 0) }}" min="0" onchange="calcTotal()">
                    <span class="form-hint">Potongan langsung dari total</span>
                </div>
                <div class="form-group">
                    <div style="background:rgba(0,212,170,0.06); border:1px solid rgba(0,212,170,0.2); border-radius:8px; padding:12px; text-align:center;">
                        <div id="duration-display" style="font-size:20px; font-weight:800; color:var(--accent-primary);">{{ $lodging->duration_days }} hari</div>
                        <div id="base-display" style="font-size:12px; color:var(--text-muted); margin-top:2px;"></div>
                        <div id="total-display" class="money-text" style="font-size:14px; color:var(--text-muted); margin-top:4px;">Rp {{ number_format($lodging->total_price, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-person-vcard"></i> Data PIC</div></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama PIC <span class="required">*</span></label>
                    <input type="text" name="pic_name" id="pic_name" class="form-control" value="{{ old('pic_name', $lodging->pic_name) }}" required>
                </div>
                <div class="form-group">
                    <label>No HP <span class="required">*</span></label>
                    <input type="text" name="pic_phone" id="pic_phone" class="form-control" value="{{ old('pic_phone', $lodging->pic_phone) }}" required>
                </div>
                <div class="form-group">
                    <label>NIK PIC</label>
                    <input type="text" name="pic_nik" id="pic_nik" class="form-control" value="{{ old('pic_nik', $lodging->pic_nik) }}" maxlength="16" style="font-family:monospace;">
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <input type="text" name="pic_address" id="pic_address" class="form-control" value="{{ old('pic_address', $lodging->pic_address) }}">
                </div>
                <div class="form-group span-full">
                    <label>Nama Tamu Lainnya</label>
                    <textarea name="guest_names" class="form-control" rows="2">{{ old('guest_names', $lodging->guest_names) }}</textarea>
                </div>
                <div class="form-group span-full">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $lodging->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-cash-coin"></i> Pembayaran</div></div>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <div class="form-group">
                    <label>Status Bayar</label>
                    <select name="payment_status" class="form-control">
                        <option value="paid"    {{ old('payment_status', $lodging->payment_status) == 'paid'    ? 'selected' : '' }}>✅ Lunas</option>
                        <option value="partial" {{ old('payment_status', $lodging->payment_status) == 'partial' ? 'selected' : '' }}>⚠️ Sebagian</option>
                        <option value="unpaid"  {{ old('payment_status', $lodging->payment_status) == 'unpaid'  ? 'selected' : '' }}>❌ Belum Bayar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Pembayaran</label>
                    <input type="date" name="paid_at" class="form-control" value="{{ old('paid_at', $lodging->paid_at?->format('Y-m-d')) }}">
                    <span class="form-hint">Mempengaruhi pencatatan omzet bulanan</span>
                </div>
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="payment_method" class="form-control">
                        <option value="">— Pilih —</option>
                        <option value="tunai"    {{ old('payment_method', $lodging->payment_method) == 'tunai'    ? 'selected' : '' }}>💵 Tunai</option>
                        <option value="transfer" {{ old('payment_method', $lodging->payment_method) == 'transfer' ? 'selected' : '' }}>🏦 Transfer</option>
                        <option value="qris"     {{ old('payment_method', $lodging->payment_method) == 'qris'     ? 'selected' : '' }}>📱 QRIS</option>
                        <option value="lain-lain"{{ old('payment_method', $lodging->payment_method) == 'lain-lain'? 'selected' : '' }}>🔖 Lain-lain</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Deposit (Rp)</label>
                    <input type="text" inputmode="numeric" name="deposit" class="form-control input-rupiah" value="{{ old('deposit', $lodging->deposit) }}" min="0">
                </div>
                <div class="form-group">
                    <label>Status Penginapan</label>
                    <select name="status" class="form-control">
                        <option value="active"    {{ old('status', $lodging->status) == 'active'    ? 'selected' : '' }}>🟢 Aktif</option>
                        <option value="completed" {{ old('status', $lodging->status) == 'completed' ? 'selected' : '' }}>✅ Selesai</option>
                        <option value="cancelled" {{ old('status', $lodging->status) == 'cancelled' ? 'selected' : '' }}>❌ Dibatalkan</option>
                    </select>
                </div>
                <div style="background:rgba(0,212,170,0.06); border:1px solid rgba(0,212,170,0.2); border-radius:8px; padding:14px;">
                    <div class="text-muted text-sm" style="margin-bottom:4px;">Total Bayar</div>
                    <div id="total-display-side" class="money-text" style="font-size:22px; font-weight:800; color:var(--accent-primary);">Rp {{ number_format($lodging->total_price, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;"><i class="bi bi-floppy"></i> Simpan</button>
                <a href="{{ route('lodgings.show', $lodging) }}" class="btn btn-secondary" style="width:100%; justify-content:center;">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
function calcDuration() {
    const ci = document.getElementById('check_in').value;
    const co = document.getElementById('check_out').value;
    if (ci && co) {
        const d1 = new Date(ci);
        const d2 = new Date(co);
        d1.setHours(0,0,0,0);
        d2.setHours(0,0,0,0);
        const diff = (d2 - d1) / (1000 * 60 * 60 * 24);
        const days = Math.max(1, Math.round(diff));
        document.getElementById('duration-display').textContent = days + ' hari';
        calcTotal(days);
    }
}
function calcTotal(days) {
    const price      = parseFloat(document.getElementById('price_per_night').value) || 0;
    const guests     = parseFloat(document.getElementById('guest_count').value) || 1;
    const dailyDisc  = parseFloat(document.getElementById('daily_discount').value) || 0;
    const fixedDisc  = parseFloat(document.getElementById('fixed_discount').value) || 0;
    
    if (!days) {
        const ci = document.getElementById('check_in').value;
        const co = document.getElementById('check_out').value;
        if (ci && co) {
            const d1 = new Date(ci);
            const d2 = new Date(co);
            d1.setHours(0,0,0,0);
            d2.setHours(0,0,0,0);
            days = Math.max(1, Math.round((d2 - d1) / 86400000));
        }
    }
    
    const netPerNight = Math.max(0, price - dailyDisc);
    const base        = netPerNight * guests * (days || 0);
    const grandTotal  = Math.max(0, base - fixedDisc);

    const baseEl = document.getElementById('base-display');
    if (days && days > 0) {
        baseEl.textContent = `(Rp ${netPerNight.toLocaleString('id-ID')}/malam × ${guests} tamu × ${days} malam)`;
    }
    
    const fmt = 'Rp ' + grandTotal.toLocaleString('id-ID');
    document.getElementById('total-display').textContent = fmt;
    document.getElementById('total-display-side').textContent = fmt;
}
function onRoomChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;
    if (!document.getElementById('pic_name').value) {
        document.getElementById('pic_name').value    = opt.dataset.tenantName    || '';
        document.getElementById('pic_phone').value   = opt.dataset.tenantPhone   || '';
        document.getElementById('pic_nik').value     = opt.dataset.tenantNik     || '';
        document.getElementById('pic_address').value = opt.dataset.tenantAddress || '';
    }
}
document.addEventListener('DOMContentLoaded', () => calcDuration());
</script>
@endpush
