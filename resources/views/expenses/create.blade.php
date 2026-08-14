@extends('layouts.app')
@section('title', 'Tambah Pengeluaran')
@section('page-title', 'Tambah Pengeluaran')
@section('topbar-actions')
    <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-receipt-cutoff"></i> Detail Pengeluaran</div></div>
        <div class="form-grid">
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
            <div class="form-group">
                <label>Nominal (Rp) <span class="required">*</span></label>
                <input type="text" inputmode="numeric" name="amount" class="form-control input-rupiah @error('amount') is-invalid @enderror"
                    value="{{ old('amount') }}" placeholder="Contoh: 850000" min="0" required>
                @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group span-full">
                <label>Judul Pengeluaran <span class="required">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}" placeholder="Contoh: Tagihan Listrik Agustus 2026" required>
                @error('title') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group span-full">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="2"
                    placeholder="Detail pengeluaran...">{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label>Tanggal Pengeluaran <span class="required">*</span></label>
                <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group">{{-- spacer --}}</div>
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
            <div class="form-group span-full">
                <label>Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-receipt"></i> Bukti / Nota</div></div>
            <div class="photo-upload-area" onclick="document.getElementById('receipt').click()">
                <i class="bi bi-file-earmark-image" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                <div style="font-size:13px;">Upload Nota/Struk</div>
            </div>
            <input type="file" id="receipt" name="receipt_photo" accept="image/*" style="display:none;"
                onchange="previewImg(this,'rp')">
            <img id="rp" style="display:none; width:100%; height:160px; object-fit:cover; border-radius:8px; margin-top:10px;">
        </div>
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <i class="bi bi-floppy"></i> Simpan Pengeluaran
                </button>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary" style="width:100%; justify-content:center;">Batal</a>
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
