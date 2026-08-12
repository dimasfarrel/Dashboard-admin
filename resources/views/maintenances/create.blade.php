@extends('layouts.app')
@section('title', 'Laporkan Maintenance')
@section('page-title', 'Laporkan Maintenance Kamar')
@section('topbar-actions')
    <a href="{{ route('maintenances.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<form action="{{ route('maintenances.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-tools"></i> Detail Maintenance</div></div>
        <div class="form-grid">
            <div class="form-group">
                <label>Kamar <span class="required">*</span></label>
                <select name="room_id" class="form-control @error('room_id') is-invalid @enderror" required>
                    <option value="">— Pilih Kamar —</option>
                    @foreach($rooms as $r)
                    <option value="{{ $r->id }}" {{ old('room_id', request('room_id')) == $r->id ? 'selected' : '' }}>Kamar {{ $r->room_number }}</option>
                    @endforeach
                </select>
                @error('room_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Kategori <span class="required">*</span></label>
                <select name="category" class="form-control @error('category') is-invalid @enderror" required>
                    <option value="">— Pilih Kategori —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ old('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group span-full">
                <label>Nama Item / Barang <span class="required">*</span></label>
                <input type="text" name="item_name" class="form-control @error('item_name') is-invalid @enderror"
                    value="{{ old('item_name') }}" placeholder="Contoh: AC 1/2 PK, Kasur Spring Bed, Kran Air" required>
                @error('item_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group span-full">
                <label>Deskripsi Kerusakan <span class="required">*</span></label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3"
                    placeholder="Jelaskan kerusakan atau masalah yang terjadi...">{{ old('description') }}</textarea>
                @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Biaya (Rp)</label>
                <input type="number" name="cost" class="form-control" value="{{ old('cost', 0) }}" min="0">
            </div>
            <div class="form-group">
                <label>Status <span class="required">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="pending"     {{ old('status','pending') == 'pending'     ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>🔵 Sedang Proses</option>
                    <option value="done"        {{ old('status') == 'done'        ? 'selected' : '' }}>✅ Selesai</option>
                    <option value="cancelled"   {{ old('status') == 'cancelled'   ? 'selected' : '' }}>❌ Dibatalkan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Laporan <span class="required">*</span></label>
                <input type="date" name="report_date" class="form-control" value="{{ old('report_date', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="done_date" class="form-control" value="{{ old('done_date') }}">
            </div>
            <div class="form-group">
                <label>Nama Vendor/Tukang</label>
                <input type="text" name="vendor" class="form-control" value="{{ old('vendor') }}" placeholder="Nama tukang atau perusahaan">
            </div>
            <div class="form-group">
                <label>No HP Vendor</label>
                <input type="text" name="vendor_phone" class="form-control" value="{{ old('vendor_phone') }}" placeholder="08xx-xxxx-xxxx">
            </div>
            <div class="form-group span-full">
                <label>Catatan Tambahan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-camera"></i> Foto</div></div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>Foto Sebelum (Before)</label>
                <div class="photo-upload-area" onclick="document.getElementById('before_photo').click()">
                    <i class="bi bi-image" style="font-size:24px; display:block; margin-bottom:4px;"></i>
                    <div style="font-size:12px;">Upload Foto Before</div>
                </div>
                <input type="file" id="before_photo" name="before_photo" accept="image/*" style="display:none;"
                    onchange="previewImg(this,'before-preview')">
                <img id="before-preview" style="display:none; width:100%; height:120px; object-fit:cover; border-radius:8px; margin-top:8px;">
            </div>
            <div class="form-group">
                <label>Foto Sesudah (After)</label>
                <div class="photo-upload-area" onclick="document.getElementById('after_photo').click()">
                    <i class="bi bi-image-fill" style="font-size:24px; display:block; margin-bottom:4px;"></i>
                    <div style="font-size:12px;">Upload Foto After</div>
                </div>
                <input type="file" id="after_photo" name="after_photo" accept="image/*" style="display:none;"
                    onchange="previewImg(this,'after-preview')">
                <img id="after-preview" style="display:none; width:100%; height:120px; object-fit:cover; border-radius:8px; margin-top:8px;">
            </div>
        </div>
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="bi bi-floppy"></i> Simpan
                </button>
                <a href="{{ route('maintenances.index') }}" class="btn btn-secondary" style="width:100%; justify-content:center;">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection
@push('scripts')
<script>
function previewImg(input, id) {
    const el = document.getElementById(id);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { el.src = e.target.result; el.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
