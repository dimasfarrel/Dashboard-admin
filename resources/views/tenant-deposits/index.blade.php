@extends('layouts.app')
@section('title', 'Laporan Deposit')
@section('page-title', 'Laporan Deposit')
@section('page-subtitle', 'Riwayat deposit masuk dan keluar penyewa')

@section('topbar-actions')
    <button type="button" class="btn btn-primary" onclick="openModal('addModal')">
        <i class="bi bi-plus-lg"></i> Tambah Deposit
    </button>
@endsection

@push('styles')
<style>
.tenant-link {
    color: #ffffffff;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s ease, text-decoration 0.2s ease;
}
.tenant-link:hover {
    color: var(--accent-primary, #00d4aa);
    text-decoration: underline;
}

.modal-backdrop {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(4, 6, 12, 0.75);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.modal-card {
    background: var(--bg-card);
    border: 1px solid var(--border-accent);
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 500px;
    padding: 24px;
    box-shadow: var(--shadow-lg);
    animation: zoomIn 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    max-height: 90vh;
    overflow-y: auto;
}
@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95); }
    to   { opacity: 1; transform: scale(1); }
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
                    <td style="text-align: right; color: {{ $deposit->type === 'credit' ? '#00d4aa' : '#ef4444' }}; font-weight: 600;">
                        {{ $deposit->type === 'credit' ? '+' : '-' }} Rp {{ number_format($deposit->amount, 0, ',', '.') }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        Rp {{ number_format($deposit->tenant->deposit_balance ?? 0, 0, ',', '.') }}
                    </td>
                    <td style="text-align: center;">
                        <div style="display:flex; justify-content:center; gap:6px;">
                            <button type="button" class="btn btn-warning btn-sm btn-icon" 
                                onclick="openEditModal({{ $deposit->id }}, '{{ $deposit->amount }}', '{{ $deposit->date->format('Y-m-d') }}', '{{ addslashes($deposit->description) }}', '{{ addslashes($deposit->notes ?? '') }}')">
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
@endsection

@push('modals')
<!-- Modal Tambah Deposit -->
<div id="addModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <div class="card-title" style="margin:0; font-size: 16px; font-weight: 700;"><i class="bi bi-safe" style="color:var(--accent-primary, #00d4aa); margin-right: 6px;"></i> Tambah Deposit</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addModal')">✕</button>
        </div>
        <form id="addForm" action="{{ route('tenant-deposits.store-global') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Penyewa <span class="required" style="color:var(--accent-red, #ef4444);">*</span></label>
                <select name="tenant_id" class="form-control" required>
                    <option value="">-- Pilih Penyewa --</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->name }} (Kamar {{ $tenant->room->room_number ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Tipe Transaksi <span class="required" style="color:var(--accent-red, #ef4444);">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="credit">Deposit Masuk (+)</option>
                    <option value="debit">Deposit Keluar/Pengurangan (-)</option>
                </select>
            </div>
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Tanggal <span class="required" style="color:var(--accent-red, #ef4444);">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Nominal (Rp) <span class="required" style="color:var(--accent-red, #ef4444);">*</span></label>
                <input type="text" inputmode="numeric" name="amount" class="form-control input-rupiah" placeholder="0" required>
            </div>
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Keterangan <span class="required" style="color:var(--accent-red, #ef4444);">*</span></label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Deposit Masuk Awal" required>
            </div>
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan bila ada..."></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Deposit</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Deposit -->
<div id="editModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <div class="card-title" style="margin:0; font-size: 16px; font-weight: 700;"><i class="bi bi-pencil" style="color:var(--accent-yellow, #eab308); margin-right: 6px;"></i> Edit Deposit</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editModal')">✕</button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Tanggal <span class="required" style="color:var(--accent-red, #ef4444);">*</span></label>
                <input type="date" name="date" id="edit_date" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Nominal (Rp) <span class="required" style="color:var(--accent-red, #ef4444);">*</span></label>
                <input type="text" inputmode="numeric" name="amount" id="edit_amount" class="form-control input-rupiah" required>
            </div>
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Keterangan <span class="required" style="color:var(--accent-red, #ef4444);">*</span></label>
                <input type="text" name="description" id="edit_description" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label style="display:block; margin-bottom:6px; font-size:13px;">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('d-none');
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('d-none');
    }
}

function openAddModal() {
    openModal('addModal');
}

function openEditModal(id, amount, date, description, notes) {
    const form = document.getElementById('editForm');
    if (form) {
        form.action = "/tenant-deposits/" + id;
    }
    
    const amountInput = document.getElementById('edit_amount');
    if (amountInput) {
        let cleanAmount = amount.toString().replace(/\..*$/, '');
        amountInput.value = typeof formatRupiah === 'function' ? formatRupiah(cleanAmount) : cleanAmount;
    }
    
    const dateInput = document.getElementById('edit_date');
    if (dateInput) dateInput.value = date;
    
    const descInput = document.getElementById('edit_description');
    if (descInput) descInput.value = description;
    
    const notesInput = document.getElementById('edit_notes');
    if (notesInput) notesInput.value = notes || '';
    
    openModal('editModal');
}

// Tutup modal jika klik di luar area modal (backdrop)
document.addEventListener('click', function(e) {
    if (e.target.classList && e.target.classList.contains('modal-backdrop')) {
        e.target.classList.add('d-none');
    }
});
</script>
@endpush
