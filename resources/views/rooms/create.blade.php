@extends('layouts.app')
@section('title', 'Tambah Kamar')
@section('page-title', 'Tambah Kamar Baru')
@section('page-subtitle', 'Isi detail kamar, harga, dan fasilitas')

@section('topbar-actions')
    <a href="{{ route('rooms.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('rooms.index') }}">Kamar</a>
    <span class="separator">/</span>
    <span class="current">Tambah Kamar</span>
</div>

<form action="{{ route('rooms.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div style="display:grid; grid-template-columns:1fr 340px; gap:24px;">

    {{-- LEFT: Main Form --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Info Dasar --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-info-circle"></i> Informasi Kamar</div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nomor Kamar <span class="required">*</span></label>
                    <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror"
                        value="{{ old('room_number') }}" placeholder="Contoh: 101, A1, 2B" required>
                    @error('room_number') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Lantai <span class="required">*</span></label>
                    <select name="floor" class="form-control @error('floor') is-invalid @enderror" required>
                        @foreach($floors as $f)
                        <option value="{{ $f->number }}" {{ old('floor', 1) == $f->number ? 'selected' : '' }}>{{ $f->name }}</option>
                        @endforeach
                    </select>
                    @error('floor') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Harga Sewa / Bulan <span class="required">*</span></label>
                    <input type="text" inputmode="numeric" name="price" class="form-control input-rupiah @error('price') is-invalid @enderror"
                        value="{{ old('price') }}" placeholder="Contoh: 1500000" min="0" required>
                    @error('price') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Status Kamar <span class="required">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>✅ Tersedia</option>
                        <option value="occupied"  {{ old('status') == 'occupied'  ? 'selected' : '' }}>🔴 Dihuni</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>🔧 Maintenance</option>
                    </select>
                    @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Tipe Kamar</label>
                    <select name="type" class="form-control">
                        <option value="">— Pilih Tipe —</option>
                        @foreach($roomTypes as $rt)
                        <option value="{{ Str::slug($rt->name) }}" {{ old('type') == Str::slug($rt->name) ? 'selected' : '' }}>{{ $rt->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Ukuran (m²)</label>
                    <input type="number" name="size_sqm" class="form-control"
                        value="{{ old('size_sqm') }}" placeholder="Contoh: 12" min="1">
                </div>

                <div class="form-group span-full">
                    <label>Deskripsi Kamar</label>
                    <textarea name="description" class="form-control" rows="3"
                        placeholder="Deskripsi singkat kondisi dan keunggulan kamar...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Fasilitas --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-check2-square"></i> Fasilitas Kamar</div>
                <span class="text-muted text-sm">Centang fasilitas yang tersedia</span>
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
                    <label class="facility-item {{ in_array($facility->id, old('facilities', [])) ? 'checked' : '' }}">
                        <input type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                            {{ in_array($facility->id, old('facilities', [])) ? 'checked' : '' }}>
                        <i class="{{ $facility->icon }}"></i>
                        <span class="facility-label">{{ $facility->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- RIGHT: Photo & Submit --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-image"></i> Foto Kamar</div>
            </div>
            <div class="form-group">
                <label>Upload Foto (opsional)</label>
                <div class="photo-upload-area" onclick="document.getElementById('room_photo').click()">
                    <i class="bi bi-cloud-upload" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                    <div style="font-size:13px; font-weight:500;">Klik untuk upload foto</div>
                    <div class="text-sm" style="margin-top:4px;">JPG, PNG, max 2MB</div>
                </div>
                <input type="file" id="room_photo" name="photo" accept="image/*"
                    style="display:none;" onchange="previewPhoto(this)">
                <img id="photo-preview" class="photo-preview" style="display:none; margin-top:12px; width:100%; max-width:none; height:180px;">
            </div>
        </div>

        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="bi bi-floppy"></i> Simpan Kamar
                </button>
                <a href="{{ route('rooms.index') }}" class="btn btn-secondary" style="width:100%; justify-content:center;">
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
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
