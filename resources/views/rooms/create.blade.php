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
                    <label>Tampilkan di Website Publik?</label>
                    <div style="margin-top: 8px;">
                        <label class="facility-item {{ old('is_published', true) ? 'checked' : '' }}">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                            <i class="bi bi-globe"></i>
                            <span class="facility-label">Ya, Publikasikan</span>
                        </label>
                    </div>
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
                <div class="card-title"><i class="bi bi-images"></i> Galeri Foto (Multiple)</div>
            </div>
            <div class="form-group">
                <label>Upload Foto Kamar</label>
                <div class="photo-upload-area" onclick="document.getElementById('room_images').click()">
                    <i class="bi bi-images" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                    <div style="font-size:13px; font-weight:500;">Klik untuk upload beberapa foto</div>
                    <div class="text-sm" style="margin-top:4px;">Bisa pilih lebih dari 1 file (JPG, PNG)</div>
                </div>
                <input type="file" id="room_images" name="images[]" accept="image/*" multiple
                    style="display:none;" onchange="previewImages(this)">
                <div id="gallery-preview" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;"></div>
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
function previewImages(input) {
    const previewContainer = document.getElementById('gallery-preview');
    previewContainer.innerHTML = ''; // Clear existing
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                previewContainer.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
}
</script>
@endpush
