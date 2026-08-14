@extends('layouts.app')
@section('title', 'Tambah Pendapatan Lain-lain')
@section('page-title', 'Tambah Pendapatan Lain-lain')
@section('topbar-actions')
    <a href="{{ route('other-incomes.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<div style="max-width:680px; margin:0 auto;">
<form action="{{ route('other-incomes.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div style="display:flex; flex-direction:column; gap:20px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-cash-coin"></i> Data Pendapatan</div></div>
        <div class="form-grid">
            <div class="form-group span-full">
                <label>Judul / Keterangan <span class="required">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}" placeholder="Contoh: Uang parkir motor bulan Agustus" required>
                @error('title') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Kategori <span class="required">*</span></label>
                <select name="category" class="form-control" required>
                    <option value="lain-lain" {{ old('category','lain-lain') == 'lain-lain' ? 'selected' : '' }}>🔖 Lain-lain</option>
                    <option value="parkir"    {{ old('category') == 'parkir'    ? 'selected' : '' }}>🅿️ Parkir</option>
                    <option value="laundry"   {{ old('category') == 'laundry'   ? 'selected' : '' }}>🧺 Laundry</option>
                    <option value="listrik"   {{ old('category') == 'listrik'   ? 'selected' : '' }}>⚡ Listrik Lebih</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nominal (Rp) <span class="required">*</span></label>
                <input type="text" inputmode="numeric" name="amount" class="form-control input-rupiah @error('amount') is-invalid @enderror"
                    value="{{ old('amount') }}" min="0" required>
                @error('amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Tanggal <span class="required">*</span></label>
                <input type="date" name="income_date" class="form-control @error('income_date') is-invalid @enderror"
                    value="{{ old('income_date', date('Y-m-d')) }}" required>
                @error('income_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Bulan Periode <span class="required">*</span></label>
                <select name="period_month" class="form-control" required>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ old('period_month', now()->month) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tahun Periode <span class="required">*</span></label>
                <input type="number" name="period_year" class="form-control"
                    value="{{ old('period_year', now()->year) }}" min="2020" required>
            </div>
            <div class="form-group span-full">
                <label>Catatan</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
            </div>
            <div class="form-group span-full">
                <label>Bukti / Foto (opsional)</label>
                <input type="file" name="receipt_photo" class="form-control" accept="image/*">
            </div>
        </div>
    </div>
    <div class="card">
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary flex-1" style="justify-content:center; padding:12px;">
                <i class="bi bi-floppy"></i> Simpan Pendapatan
            </button>
            <a href="{{ route('other-incomes.index') }}" class="btn btn-secondary" style="padding:12px 20px;">Batal</a>
        </div>
    </div>
</div>
</form>
</div>
@endsection
