@extends('layouts.app')
@section('title', 'Edit Maintenance')
@section('page-title', 'Edit Maintenance')
@section('topbar-actions')
    <a href="{{ route('maintenances.show', $maintenance) }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<form action="{{ route('maintenances.update', $maintenance) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-tools"></i> Edit Maintenance</div></div>
        <div class="form-grid">
            <div class="form-group">
                <label>Kamar <span class="required">*</span></label>
                <select name="room_id" class="form-control" required>
                    @foreach($rooms as $r)
                    <option value="{{ $r->id }}" {{ old('room_id', $maintenance->room_id) == $r->id ? 'selected' : '' }}>Kamar {{ $r->room_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Kategori <span class="required">*</span></label>
                <select name="category" class="form-control" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ old('category', $maintenance->category) == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group span-full">
                <label>Nama Item <span class="required">*</span></label>
                <input type="text" name="item_name" class="form-control" value="{{ old('item_name', $maintenance->item_name) }}" required>
            </div>
            <div class="form-group span-full">
                <label>Deskripsi <span class="required">*</span></label>
                <textarea name="description" class="form-control" rows="3" required>{{ old('description', $maintenance->description) }}</textarea>
            </div>
            <div class="form-group">
                <label>Biaya (Rp)</label>
                <input type="text" inputmode="numeric" name="cost" class="form-control input-rupiah" value="{{ old('cost', $maintenance->cost) }}" min="0">
            </div>
            <div class="form-group">
                <label>Status <span class="required">*</span></label>
                <select name="status" class="form-control" required>
                    @foreach(['pending'=>'⏳ Pending','in_progress'=>'🔵 Proses','done'=>'✅ Selesai','cancelled'=>'❌ Batal'] as $val=>$lbl)
                    <option value="{{ $val }}" {{ old('status', $maintenance->status) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tgl Laporan <span class="required">*</span></label>
                <input type="date" name="report_date" class="form-control" value="{{ old('report_date', $maintenance->report_date->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label>Tgl Selesai</label>
                <input type="date" name="done_date" class="form-control" value="{{ old('done_date', $maintenance->done_date?->format('Y-m-d')) }}">
            </div>
            <div class="form-group">
                <label>Vendor</label>
                <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $maintenance->vendor) }}">
            </div>
            <div class="form-group">
                <label>HP Vendor</label>
                <input type="text" name="vendor_phone" class="form-control" value="{{ old('vendor_phone', $maintenance->vendor_phone) }}">
            </div>
            <div class="form-group span-full">
                <label>Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $maintenance->notes) }}</textarea>
            </div>
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-camera"></i> Foto</div></div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>Foto Before</label>
                @if($maintenance->before_photo)
                <img src="{{ asset('storage/' . $maintenance->before_photo) }}" style="width:100%; height:110px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                @endif
                <input type="file" name="before_photo" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Foto After</label>
                @if($maintenance->after_photo)
                <img src="{{ asset('storage/' . $maintenance->after_photo) }}" style="width:100%; height:110px; object-fit:cover; border-radius:8px; margin-bottom:8px;">
                @endif
                <input type="file" name="after_photo" class="form-control" accept="image/*">
            </div>
        </div>
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;"><i class="bi bi-floppy"></i> Simpan</button>
                <a href="{{ route('maintenances.show', $maintenance) }}" class="btn btn-secondary" style="width:100%; justify-content:center;">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection
