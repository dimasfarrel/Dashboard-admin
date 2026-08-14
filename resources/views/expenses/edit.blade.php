@extends('layouts.app')
@section('title', 'Edit Pengeluaran')
@section('page-title', 'Edit Pengeluaran')
@section('topbar-actions')
    <a href="{{ route('expenses.show', $expense) }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
@endsection
@section('content')
<form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
@csrf @method('PUT')
<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div class="card">
        <div class="card-header"><div class="card-title"><i class="bi bi-receipt-cutoff"></i> Edit Pengeluaran</div></div>
        <div class="form-grid">
            <div class="form-group">
                <label>Kategori <span class="required">*</span></label>
                <select name="category" class="form-control" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ old('category', $expense->category) == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Nominal <span class="required">*</span></label>
                <input type="text" inputmode="numeric" name="amount" class="form-control input-rupiah" value="{{ old('amount', $expense->amount) }}" required>
            </div>
            <div class="form-group span-full">
                <label>Judul <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $expense->title) }}" required>
            </div>
            <div class="form-group span-full">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $expense->description) }}</textarea>
            </div>
            <div class="form-group">
                <label>Tanggal <span class="required">*</span></label>
                <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label>Periode Bulan <span class="required">*</span></label>
                <select name="period_month" class="form-control" required>
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ old('period_month', $expense->period_month) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tahun</label>
                <input type="number" name="period_year" class="form-control" value="{{ old('period_year', $expense->period_year) }}" required>
            </div>
            <div class="form-group span-full">
                <label>Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $expense->notes) }}</textarea>
            </div>
        </div>
    </div>
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="card">
            <div class="card-header"><div class="card-title"><i class="bi bi-receipt"></i> Bukti/Nota</div></div>
            @if($expense->receipt_photo)
            <img src="{{ asset('storage/' . $expense->receipt_photo) }}" style="width:100%; height:140px; object-fit:cover; border-radius:8px; margin-bottom:10px;">
            @endif
            <input type="file" name="receipt_photo" class="form-control" accept="image/*">
        </div>
        <div class="card">
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;"><i class="bi bi-floppy"></i> Simpan</button>
                <a href="{{ route('expenses.show', $expense) }}" class="btn btn-secondary" style="width:100%; justify-content:center;">Batal</a>
            </div>
        </div>
    </div>
</div>
</form>
@endsection
