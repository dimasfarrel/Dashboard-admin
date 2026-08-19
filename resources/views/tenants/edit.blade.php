@extends('layouts.app')
@section('title', 'Edit Penyewa — ' . $tenant->name)
@section('page-title', 'Edit Data Penyewa')

@section('topbar-actions')
    <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
@php
    $isLocked = $tenant->status === 'inactive';
@endphp
@if($isLocked)
<div class="alert alert-warning" id="locked-warning" style="display:flex; align-items:center;">
    <i class="bi bi-lock-fill" style="margin-right:8px; font-size:18px;"></i>
    <div><strong>Data Riwayat Terkunci.</strong> Data penyewa non-aktif tidak dapat diubah.</div>
    <button type="button" class="btn btn-sm btn-dark" style="margin-left: auto;" onclick="document.getElementById('tenant-fieldset').disabled = false; this.parentElement.style.display='none';">
        <i class="bi bi-unlock-fill"></i> Buka Kunci (Admin)
    </button>
</div>
@endif

<form action="{{ route('tenants.update', $tenant) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<fieldset id="tenant-fieldset" {{ $isLocked ? 'disabled' : '' }} style="border:none; padding:0; margin:0; min-width:0;">


<div style="display:grid; grid-template-columns:1fr 340px; gap:24px;">
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-person-badge"></i> Data Diri</div></div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $tenant->name) }}" required>
                    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Nama Panggilan</label>
                    <input type="text" name="nickname" class="form-control @error('nickname') is-invalid @enderror"
                        value="{{ old('nickname', $tenant->nickname) }}" placeholder="Nama panggilan">
                    @error('nickname') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Nomor Identitas <span class="required">*</span></label>
                    <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                        value="{{ old('nik', $tenant->nik) }}" style="font-family:monospace; letter-spacing:1px;" required>
                    @error('nik') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>WhatsApp <span class="required">*</span></label>
                    <input type="text" name="phone_wa" class="form-control" value="{{ old('phone_wa', $tenant->phone_wa) }}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="active"   {{ old('status', $tenant->status) == 'active'   ? 'selected' : '' }}>✅ Aktif</option>
                        <option value="inactive" {{ old('status', $tenant->status) == 'inactive' ? 'selected' : '' }}>❌ Tidak Aktif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="gender" class="form-control">
                        <option value="">— Pilih —</option>
                        <option value="laki-laki"  {{ old('gender', $tenant->gender) == 'laki-laki'  ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan"   {{ old('gender', $tenant->gender) == 'perempuan'  ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $tenant->birth_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label>Pekerjaan</label>
                    <input type="text" name="occupation" class="form-control" value="{{ old('occupation', $tenant->occupation) }}">
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="origin_city" class="form-control" value="{{ old('origin_city', $tenant->origin_city) }}">
                </div>
                <div class="form-group">
                    <label>Kontak Darurat</label>
                    <input type="text" name="emergency_contact_name" class="form-control"
                        value="{{ old('emergency_contact_name', $tenant->emergency_contact_name) }}">
                </div>
                <div class="form-group">
                    <label>HP Darurat</label>
                    <input type="text" name="emergency_contact_phone" class="form-control"
                        value="{{ old('emergency_contact_phone', $tenant->emergency_contact_phone) }}">
                </div>
                <div class="form-group mt-3">
                    <label>Kontak Darurat 2 <small class="text-muted">(Opsional)</small></label>
                    <input type="text" name="emergency_contact_name_2" class="form-control"
                        value="{{ old('emergency_contact_name_2', $tenant->emergency_contact_name_2) }}">
                </div>
                <div class="form-group mt-3">
                    <label>HP Darurat 2 <small class="text-muted">(Opsional)</small></label>
                    <input type="text" name="emergency_contact_phone_2" class="form-control"
                        value="{{ old('emergency_contact_phone_2', $tenant->emergency_contact_phone_2) }}">
                </div>
                <div class="form-group">
                    <label>Kamar <span class="required">*</span></label>
                    <select name="room_id" class="form-control" required>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id', $tenant->room_id) == $room->id ? 'selected' : '' }}>
                            Kamar {{ $room->room_number }} ({{ ucfirst($room->status) }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">{{-- spacer --}}</div>
                <div class="form-group">
                    <label>Mulai Sewa <span class="required">*</span></label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $tenant->start_date->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label>Berakhir Kontrak</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $tenant->end_date?->format('Y-m-d')) }}">
                </div>
                <div class="form-group span-full">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $tenant->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Custom Fields --}}
        @if($customFields->count())
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-sliders"></i> Informasi Tambahan</div>
                <a href="{{ route('settings.index', ['tab' => 'tenant_fields']) }}" class="btn btn-secondary btn-sm"><i class="bi bi-gear"></i></a>
            </div>
            <div class="form-grid">
                @foreach($customFields as $field)
                <div class="form-group {{ in_array($field->type, ['textarea']) ? 'span-full' : '' }}">
                    <label>{{ $field->name }} @if($field->is_required)<span class="required">*</span>@endif</label>
                    @php $fieldVal = $fieldValues[$field->field_key] ?? old("custom_field.{$field->field_key}", ''); @endphp
                    @if($field->type === 'textarea')
                        <textarea name="custom_field[{{ $field->field_key }}]" class="form-control" rows="2"
                            {{ $field->is_required ? 'required' : '' }}>{{ $fieldVal }}</textarea>
                    @elseif($field->type === 'date')
                        <input type="date" name="custom_field[{{ $field->field_key }}]" class="form-control"
                            value="{{ $fieldVal }}" {{ $field->is_required ? 'required' : '' }}>
                    @elseif($field->type === 'number')
                        <input type="number" name="custom_field[{{ $field->field_key }}]" class="form-control"
                            value="{{ $fieldVal }}" placeholder="{{ $field->placeholder ?? '' }}"
                            {{ $field->is_required ? 'required' : '' }}>
                    @else
                        <input type="text" name="custom_field[{{ $field->field_key }}]" class="form-control"
                            value="{{ $fieldVal }}" placeholder="{{ $field->placeholder ?? '' }}"
                            {{ $field->is_required ? 'required' : '' }}>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-card-image"></i> Dokumen</div></div>
            <div class="form-group" style="margin-bottom:16px;">
                <label>Foto KTP</label>
                @if($tenant->ktp_photo)
                <img src="{{ asset('storage/' . $tenant->ktp_photo) }}" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                @endif
                <input type="file" name="ktp_photo" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Selfie + KTP</label>
                @if($tenant->selfie_photo)
                <img src="{{ asset('storage/' . $tenant->selfie_photo) }}" style="width:100%; height:120px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                @endif
                <input type="file" name="selfie_photo" class="form-control" accept="image/*">
            </div>
        </div>

        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="bi bi-floppy"></i> Simpan Perubahan
                </button>
                <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-secondary" style="width:100%; justify-content:center;">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
            </div>
        </div>
    </div>
</div>
</fieldset>
</form>
@endsection
