@extends('layouts.app')
@section('title', 'Laporan Deposit')
@section('page-title', 'Laporan Deposit')
@section('page-subtitle', 'Riwayat deposit masuk dan keluar penyewa')

@section('topbar-actions')
    <button type="button" class="btn btn-primary" onclick="openAddModal()">
        <i class="bi bi-plus-lg"></i> Tambah Deposit
    </button>
@endsection

@push('styles')
<style>
.tenant-link {
    color: #ffffffff; /* Profesional blue for dark mode */
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s ease, text-decoration 0.2s ease;
}
.tenant-link:hover {
    color: #309227ff;
    text-decoration: underline;
}
</style>
@endpush

@section('content')
<!-- Filter Bar -->
<div class="filter-bar" style="margin-bottom: 20px; background: var(--bg-card); padding: 15px; border-radius: 12px; border: 1px solid var(--border-accent, #334155);">
    <form method="GET" action="{{ route('tenant-deposits.index') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <div>
            <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block;">Bulan</label>
            <select name="month" class="form-control" style="width: 150px;">
                <option value="">Semua Bulan</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::now()->setMonth((int)($m))->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block;">Tahun</label>
            <input type="number" name="year" class="form-control" style="width: 100px;" value="{{ request('year', date('Y')) }}" placeholder="Tahun">
        </div>
        <div>
            <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block;">Nama Penyewa</label>
            <input type="text" name="name" class="form-control" style="width: 200px;" value="{{ request('name') }}" placeholder="Cari nama penyewa...">
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
            <a href="{{ route('tenant-deposits.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Semua Transaksi Deposit</h3>
    </div>
    
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Penyewa</th>
                    <th>Tipe</th>
                    <th>Keterangan</th>
                    <th style="text-align: right;">Nominal Deposit</th>
                    <th style="text-align: right;">Saldo Deposit Saat Ini</th>
                    <th style="text-align: center; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $deposit)
                <tr>
                    <td>{{ $deposit->date->format('d-m-Y') }}</td>
                    <td>
                        <a href="{{ route('tenants.show', $deposit->tenant_id) }}" class="tenant-link">
                            {{ $deposit->tenant->name ?? 'N/A' }}
                        </a>
                    </td>
                    <td>
                        @if($deposit->type === 'credit')
                            <span class="badge" style="background:rgba(0,212,170,0.12); color:#00d4aa; border:1px solid rgba(0,212,170,0.2);">Masuk</span>
                        @else
                            <span class="badge" style="background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.2);">Keluar</span>
                        @endif
                    </td>
                    <td>{{ $deposit->description }}</td>
                    <td style="text-align: right; color: {{ $deposit->type === 'credit' ? '#00d4aa' : '#ef4444' }};">
                        {{ $deposit->type === 'credit' ? '+' : '-' }} Rp {{ number_format($deposit->amount, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        Rp {{ number_format($deposit->tenant->deposit_balance ?? 0, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        <div style="display:flex; justify-content:center; gap:6px;">
                            <button type="button" class="btn btn-warning btn-sm btn-icon" 
                                onclick="openEditModal({{ $deposit->id }}, '{{ $deposit->amount }}', '{{ $deposit->date->format('Y-m-d') }}', '{{ addslashes($deposit->description) }}', '{{ addslashes($deposit->notes) }}')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('tenant-deposits.destroy', $deposit) }}" method="POST" data-confirm="Yakin ingin menghapus data deposit ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">
                        Belum ada data transaksi deposit.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($deposits->hasPages())
    <div style="margin-top: 20px;">
        {{ $deposits->links('components.pagination') }}
    </div>
    @endif
</div>


@push('modals')
<!-- Modal Tambah Deposit -->
<div id="addModal" class="modal-backdrop d-none">
    <div class="modal-card" style="max-width: 500px;">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-safe" style="color:var(--primary-color);"></i> Tambah Deposit</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addModal')">✕</button>
        </div>
        <form id="addForm" action="{{ route('tenant-deposits.store-global') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Penyewa <span class="required">*</span></label>
                <select name="tenant_id" class="form-control" required>
                    <option value="">-- Pilih Penyewa --</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->name }} (Kamar {{ $tenant->room->room_number ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-4">
                <label>Tipe Transaksi <span class="required">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="credit">Deposit Masuk (+)</option>
                    <option value="debit">Deposit Keluar/Pengurangan (-)</option>
                </select>
            </div>
            <div class="form-group mb-4">
                <label>Tanggal <span class="required">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group mb-4">
                <label>Nominal (Rp) <span class="required">*</span></label>
                <input type="text" name="amount" class="form-control money-input" required>
            </div>
            <div class="form-group mb-4">
                <label>Keterangan <span class="required">*</span></label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Deposit Kamar 101" required>
            </div>
            <div class="form-group mb-4">
                <label>Catatan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Deposit -->
<div id="editModal" class="modal-backdrop d-none">
    <div class="modal-card" style="max-width: 500px;">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-pencil" style="color:#eab308;"></i> Edit Deposit</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editModal')">✕</button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label>Tanggal <span class="required">*</span></label>
                <input type="date" name="date" id="edit_date" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Nominal (Rp) <span class="required">*</span></label>
                <input type="text" name="amount" id="edit_amount" class="form-control money-input" required>
            </div>
            <div class="form-group mb-4">
                <label>Keterangan <span class="required">*</span></label>
                <input type="text" name="description" id="edit_description" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Catatan Tambahan (Opsional)</label>
                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('d-none');
}

function closeModal(id) {
    document.getElementById(id).classList.add('d-none');
}

function openAddModal() {
    openModal('addModal');
}

function openEditModal(id, amount, date, description, notes) {
    document.getElementById('editForm').action = "/tenant-deposits/" + id;
    
    // Format amount with dots
    let formattedAmount = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    
    document.getElementById('edit_amount').value = formattedAmount;
    document.getElementById('edit_date').value = date;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_notes').value = notes;
    
    openModal('editModal');
}

document.addEventListener('DOMContentLoaded', function() {
    // Money input formatting
    const moneyInputs = document.querySelectorAll('.money-input');
    moneyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value !== '') {
                value = parseInt(value, 10).toLocaleString('id-ID');
                this.value = value;
            } else {
                this.value = '';
            }
        });
    });
});
</script>
@endpush
@endsection
