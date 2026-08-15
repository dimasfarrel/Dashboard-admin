@extends('layouts.app')
@section('title', 'Tambah Penyewa')
@section('page-title', 'Tambah Data Penyewa')
@section('page-subtitle', 'Isi data lengkap penyewa baru')

@section('topbar-actions')
    <a href="{{ route('tenants.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('tenants.index') }}">Penyewa</a>
    <span class="separator">/</span>
    <span class="current">Tambah Penyewa</span>
</div>

<form action="{{ route('tenants.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div style="display:grid; grid-template-columns:1fr 340px; gap:24px;">
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Data Diri --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-person-badge"></i> Data Diri Penyewa</div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" placeholder="Sesuai KTP" required>
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Nama Panggilan</label>
                    <input type="text" name="nickname" class="form-control @error('nickname') is-invalid @enderror"
                        value="{{ old('nickname') }}" placeholder="Nama yang biasa dipanggil">
                    @error('nickname') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Nomor Identitas <span class="required">*</span></label>
                    <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                        value="{{ old('nik') }}" placeholder="No. KTP / Paspor / dll"
                        style="font-family:monospace; letter-spacing:1px;" required>
                    @error('nik') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Nomor WhatsApp <span class="required">*</span></label>
                    <input type="text" name="phone_wa" class="form-control @error('phone_wa') is-invalid @enderror"
                        value="{{ old('phone_wa') }}" placeholder="08xx-xxxx-xxxx" required>
                    @error('phone_wa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="gender" class="form-control">
                        <option value="">— Pilih —</option>
                        <option value="laki-laki"  {{ old('gender') == 'laki-laki'  ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan"   {{ old('gender') == 'perempuan'  ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date') }}">
                </div>

            

                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="origin_city" class="form-control"
                        value="{{ old('origin_city') }}" placeholder="Contoh: Jl. Sudirman No.1, Surabaya">
                </div>

                <div class="form-group span-full">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control" rows="2"
                        placeholder="Catatan tambahan tentang penyewa...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Custom Fields --}}
        @if($customFields->count())
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-sliders"></i> Informasi Tambahan</div>
                <a href="{{ route('settings.index', ['tab' => 'tenant_fields']) }}" class="btn btn-secondary btn-sm" title="Kelola field tambahan">
                    <i class="bi bi-gear"></i>
                </a>
            </div>
            <div class="form-grid">
                @foreach($customFields as $field)
                <div class="form-group {{ in_array($field->type, ['textarea']) ? 'span-full' : '' }}">
                    <label>{{ $field->name }} @if($field->is_required)<span class="required">*</span>@endif</label>
                    @if($field->type === 'textarea')
                        <textarea name="custom_field[{{ $field->field_key }}]" class="form-control" rows="2"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            {{ $field->is_required ? 'required' : '' }}>{{ old("custom_field.{$field->field_key}") }}</textarea>
                    @elseif($field->type === 'date')
                        <input type="date" name="custom_field[{{ $field->field_key }}]" class="form-control"
                            value="{{ old("custom_field.{$field->field_key}") }}"
                            {{ $field->is_required ? 'required' : '' }}>
                    @elseif($field->type === 'number')
                        <input type="number" name="custom_field[{{ $field->field_key }}]" class="form-control"
                            value="{{ old("custom_field.{$field->field_key}") }}"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            {{ $field->is_required ? 'required' : '' }}>
                    @else
                        <input type="text" name="custom_field[{{ $field->field_key }}]" class="form-control"
                            value="{{ old("custom_field.{$field->field_key}") }}"
                            placeholder="{{ $field->placeholder ?? '' }}"
                            {{ $field->is_required ? 'required' : '' }}>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div style="background:rgba(255,255,255,0.03); border:1px dashed var(--border-color); border-radius:12px; padding:16px; text-align:center;">
            <div class="text-muted text-sm">
                <i class="bi bi-plus-circle" style="font-size:20px; display:block; margin-bottom:6px;"></i>
                Belum ada field tambahan. <a href="{{ route('settings.index', ['tab' => 'tenant_fields']) }}" style="color:var(--accent-primary);">Tambah dari Pengaturan</a>
            </div>
        </div>
        @endif

        {{-- Kontak Darurat --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-telephone-fill"></i> Kontak Darurat</div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Kontak Darurat</label>
                    <input type="text" name="emergency_contact_name" class="form-control"
                        value="{{ old('emergency_contact_name') }}" placeholder="Nama keluarga/kerabat">
                </div>
                <div class="form-group">
                    <label>Nomor HP Kontak Darurat</label>
                    <input type="text" name="emergency_contact_phone" class="form-control"
                        value="{{ old('emergency_contact_phone') }}" placeholder="08xx-xxxx-xxxx">
                </div>
            </div>
        </div>

        {{-- Data Sewa --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-calendar-check"></i> Data Sewa</div>
            </div>
            <div class="form-grid">
                <div class="form-group span-full">
                    <label>Status Input <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>🟢 Penyewa Aktif Sekarang</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>📂 Input Riwayat Lama (Non-Aktif)</option>
                    </select>
                    <span class="form-hint">Pilih "Input Riwayat Lama" jika Anda memasukkan data penyewa yang sudah keluar.</span>
                </div>

                <div class="form-group">
                    <label>Kamar <span class="required">*</span></label>
                    <select name="room_id" class="form-control @error('room_id') is-invalid @enderror" required>
                        <option value="">— Pilih Kamar —</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}"
                            {{ old('room_id', request('room_id')) == $room->id ? 'selected' : '' }}>
                            Kamar {{ $room->room_number }} 
                            @if($room->status == 'occupied') (Sedang Dihuni: {{ $room->tenant?->name }}) @endif
                            — Rp {{ number_format($room->price, 0, ',', '.') }}/bln
                        </option>
                        @endforeach
                    </select>
                    @error('room_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">{{-- spacer --}}</div>

                <div class="form-group">
                    <label>Tanggal Mulai Sewa <span class="required">*</span></label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                        value="{{ old('start_date', date('Y-m-d')) }}" required>
                    @error('start_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Tanggal Berakhir Kontrak</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                        value="{{ old('end_date') }}">
                    @error('end_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    <span class="form-hint">Opsional — kosongkan jika tidak ada kontrak tetap</span>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Foto & Submit --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-card-image"></i> Dokumen Foto</div>
            </div>

            <div class="form-group" style="margin-bottom:16px;">
                <label>Foto KTP</label>
                <div class="photo-upload-area" onclick="document.getElementById('ktp_photo').click()">
                    <i class="bi bi-credit-card" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                    <div style="font-size:13px;">Upload Foto KTP</div>
                    <div class="text-sm" style="margin-top:4px;">JPG, PNG, max 3MB</div>
                </div>
                <input type="file" id="ktp_photo" name="ktp_photo" accept="image/*"
                    style="display:none;" onchange="previewImg(this, 'ktp-preview')">
                <img id="ktp-preview" class="photo-preview" style="display:none; margin-top:10px; width:100%; max-width:none; height:140px;">
            </div>

            <div class="form-group">
                <label>Foto Selfie dengan KTP</label>
                <div class="photo-upload-area" onclick="document.getElementById('selfie_photo').click()">
                    <i class="bi bi-person-square" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                    <div style="font-size:13px;">Upload Foto Selfie + KTP</div>
                </div>
                <input type="file" id="selfie_photo" name="selfie_photo" accept="image/*"
                    style="display:none;" onchange="previewImg(this, 'selfie-preview')">
                <img id="selfie-preview" class="photo-preview" style="display:none; margin-top:10px; width:100%; max-width:none; height:140px;">
            </div>
        </div>

        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="bi bi-person-check"></i> Simpan Data Penyewa
                </button>
                <a href="{{ route('tenants.index') }}" class="btn btn-secondary" style="width:100%; justify-content:center;">
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
function previewImg(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
