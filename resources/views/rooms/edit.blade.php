@extends('layouts.app')
@section('title', 'Edit Kamar ' . $room->room_number)
@section('page-title', 'Edit Kamar ' . $room->room_number)
@section('page-subtitle', 'Perbarui informasi, harga, dan fasilitas kamar')

@section('topbar-actions')
    <a href="{{ route('rooms.show', $room) }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('rooms.index') }}">Kamar</a>
    <span class="separator">/</span>
    <a href="{{ route('rooms.show', $room) }}">Kamar {{ $room->room_number }}</a>
    <span class="separator">/</span>
    <span class="current">Edit</span>
</div>

<form action="{{ route('rooms.update', $room) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')

<div style="display:grid; grid-template-columns:1fr 340px; gap:24px;">
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-info-circle"></i> Informasi Kamar</div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nomor Kamar <span class="required">*</span></label>
                    <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror"
                        value="{{ old('room_number', $room->room_number) }}" required>
                    @error('room_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Lantai <span class="required">*</span></label>
                    <select name="floor" class="form-control" required>
                        @foreach($floors as $f)
                        <option value="{{ $f->number }}" {{ old('floor', $room->floor) == $f->number ? 'selected' : '' }}>{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga Sewa / Bulan <span class="required">*</span></label>
                    <input type="text" inputmode="numeric" name="price" class="form-control input-rupiah @error('price') is-invalid @enderror"
                        value="{{ old('price', $room->price) }}" min="0" required>
                    @error('price') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Status Kamar <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="available"   {{ old('status', $room->status) == 'available'   ? 'selected' : '' }}>✅ Tersedia</option>
                        <option value="occupied"    {{ old('status', $room->status) == 'occupied'    ? 'selected' : '' }}>🔴 Dihuni</option>
                        <option value="maintenance" {{ old('status', $room->status) == 'maintenance' ? 'selected' : '' }}>🔧 Maintenance</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipe Kamar</label>
                    <select name="type" class="form-control">
                        <option value="">— Pilih Tipe —</option>
                        @foreach($roomTypes as $rt)
                        <option value="{{ Str::slug($rt->name) }}" {{ old('type', $room->type) == Str::slug($rt->name) ? 'selected' : '' }}>{{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Ukuran (m²)</label>
                    <input type="number" name="size_sqm" class="form-control"
                        value="{{ old('size_sqm', $room->size_sqm) }}" min="1">
                </div>
                <div class="form-group span-full">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $room->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-check2-square"></i> Fasilitas Kamar</div>
            </div>
            @foreach($facilities as $category => $items)
            <div class="facility-section">
                <div class="facility-section-title">
                    @php
                    $catLabel = match($category) {
                        'furnitur'    => '🪑 Furnitur',
                        'elektronik'  => '⚡ Elektronik',
                        'kamar_mandi' => '🚿 Kamar Mandi',
                        'lainnya'     => '✨ Lainnya',
                        default       => ucfirst($category),
                    };
                    @endphp
                    {{ $catLabel }}
                </div>
                <div class="facility-grid">
                    @foreach($items as $facility)
                    @php $checked = in_array($facility->id, old('facilities', $roomFacIds)); @endphp
                    <label class="facility-item {{ $checked ? 'checked' : '' }}">
                        <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" {{ $checked ? 'checked' : '' }}>
                        <i class="{{ $facility->icon }}"></i>
                        <span class="facility-label">{{ $facility->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-image"></i> Foto Kamar</div>
            </div>
            @if($room->photo)
            <img src="{{ asset('storage/' . $room->photo) }}" class="photo-preview" style="width:100%; max-width:none; height:180px; margin-bottom:12px;">
            <div class="text-muted text-sm" style="margin-bottom:10px;">Upload baru untuk mengganti foto</div>
            @endif
            <div class="photo-upload-area" onclick="document.getElementById('room_photo').click()">
                <i class="bi bi-cloud-upload" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                <div style="font-size:13px;">Klik untuk upload foto baru</div>
            </div>
            <input type="file" id="room_photo" name="photo" accept="image/*"
                style="display:none;" onchange="previewPhoto(this)">
            <img id="photo-preview" class="photo-preview" style="display:none; margin-top:12px; width:100%; max-width:none; height:180px;">
        </div>

        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="bi bi-floppy"></i> Simpan Perubahan
                </button>
                <a href="{{ route('rooms.show', $room) }}" class="btn btn-secondary" style="width:100%; justify-content:center;">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection

@push('scripts')
<script>
function previewPhoto(input) {
    const preview = document.getElementById('photo-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
