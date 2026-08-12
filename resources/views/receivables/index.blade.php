@extends('layouts.app')
@section('title', 'Piutang Kost')
@section('page-title', 'Piutang Kost')
@section('page-subtitle', 'Manajemen data uang masuk dari kasbon atau hutang orang lain')

@section('topbar-actions')
    <button class="btn btn-primary" onclick="openModal('addLoanModal')">
        <i class="bi bi-plus-lg"></i> Tambah Piutang
    </button>
    <button class="btn btn-success" onclick="openModal('addGlobalRepaymentModal')">
        <i class="bi bi-cash"></i> Tambah Pelunasan
    </button>
@endsection

@section('content')

{{-- Summary Cards --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px;">
    <div class="stat-card" style="--accent-color:#3b82f6;">
        <div class="stat-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;"><i class="bi bi-bank"></i></div>
        <div><div class="stat-label">Total Piutang</div>
        <div class="stat-value">{{ $totalLoansCount }} <span class="text-xs text-muted fw-400">data</span></div></div>
    </div>
    <div class="stat-card" style="--accent-color:#00d4aa;">
        <div class="stat-icon" style="background:rgba(0,212,170,0.12);color:#00d4aa;"><i class="bi bi-check-circle"></i></div>
        <div><div class="stat-label">Sudah Lunas</div>
        <div class="stat-value">{{ $paidLoansCount }} <span class="text-xs text-muted fw-400">data</span></div></div>
    </div>
    <div class="stat-card" style="--accent-color:#eab308;">
        <div class="stat-icon" style="background:rgba(234,179,8,0.12);color:#eab308;"><i class="bi bi-clock-history"></i></div>
        <div><div class="stat-label">Belum Lunas</div>
        <div class="stat-value">{{ $unpaidLoansCount }} <span class="text-xs text-muted fw-400">data</span></div></div>
    </div>
    <div class="stat-card" style="--accent-color:#a855f7;">
        <div class="stat-icon" style="background:rgba(168,85,247,0.12);color:#a855f7;"><i class="bi bi-wallet2"></i></div>
        <div>
            <div class="stat-label">Total Nominal Piutang</div>
            <div class="stat-value small money-text">Rp {{ number_format($totalLoansAmount, 0, ',', '.') }}</div>
            <div class="text-xs text-muted mt-1">Telah dibayar: Rp {{ number_format($paidAmount, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="filter-bar" style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px;">
    <form method="GET" id="filterForm">
        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
            <div>
                <label class="text-xs text-muted" style="display:block; margin-bottom:4px;">Status</label>
                <select name="status" class="form-control" style="width:150px;">
                    <option value="">Semua Status</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>✅ Lunas</option>
                    <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>⏳ Belum Lunas</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-muted" style="display:block; margin-bottom:4px;">Bulan</label>
                <select name="month" class="form-control" style="width:130px;">
                    <option value="">Semua Bulan</option>
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ $i }}" {{ (isset($month) && $month == $i) ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="text-xs text-muted" style="display:block; margin-bottom:4px;">Tahun</label>
                <select name="year" class="form-control" style="width:100px;">
                    <option value="">Semua Tahun</option>
                    @php $currentYear = date('Y'); @endphp
                    @for($i = $currentYear - 2; $i <= $currentYear + 2; $i++)
                        <option value="{{ $i }}" {{ (isset($year) && $year == $i) ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('receivables.index') }}" class="btn btn-secondary">Reset</a>
                <button type="button" onclick="window.print()" class="btn btn-info"><i class="bi bi-printer"></i> Cetak</button>
            </div>
        </div>
    </form>
</div>

<style>
@media print {
    .sidebar, .topbar, .filter-bar, .card-header .btn, td:last-child, th:last-child, .stat-icon, .btn {
        display: none !important;
    }
    body { background: white !important; padding: 0 !important; color: black !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; margin: 0 !important; }
    .table-wrapper { overflow: visible !important; }
    table { border-collapse: collapse !important; width: 100% !important; }
    th, td { border: 1px solid #ccc !important; padding: 8px !important; color: black !important; }
    .badge { border: 1px solid #666 !important; color: black !important; background: none !important; }
    .stat-card { border: 1px solid #ccc !important; box-shadow: none !important; background: white !important; color: black !important; }
    .stat-label { color: #666 !important; }
    .stat-value { color: black !important; }
}
</style>

{{-- Table --}}
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="bi bi-table"></i> Data Piutang ({{ $loans->total() }})</div>
    </div>
    @if($loans->count())
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tgl Piutang</th>
                    <th>Nama Peminjam</th>
                    <th>Keperluan</th>
                    <th>Total Piutang</th>
                    <th>Sisa Piutang</th>
                    <th>Status</th>
                    <th style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loans as $loan)
                <tr>
                    <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                    <td class="fw-600">{{ $loan->name }}</td>
                    <td>{{ Str::limit($loan->purpose, 40) }}</td>
                    <td class="money-text">Rp {{ number_format($loan->total_amount, 0, ',', '.') }}</td>
                    <td class="money-text text-danger">Rp {{ number_format($loan->remaining_amount, 0, ',', '.') }}</td>
                    <td>
                        @if($loan->is_paid)
                            <span class="badge badge-success">Lunas</span>
                        @else
                            <span class="badge badge-warning">Belum Lunas</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('receivables.show', $loan) }}" class="btn btn-info btn-sm btn-icon"><i class="bi bi-eye"></i></a>
                            <button type="button" class="btn btn-warning btn-sm btn-icon" 
                                data-id="{{ $loan->id }}" 
                                data-name="{{ $loan->name }}" 
                                data-purpose="{{ $loan->purpose }}" 
                                data-date="{{ $loan->loan_date->format('Y-m-d') }}" 
                                data-amount="{{ $loan->total_amount }}" 
                                data-notes="{{ $loan->notes }}" 
                                onclick="openEditLoanModal(this)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('receivables.destroy', $loan) }}" method="POST" data-confirm="Hapus data piutang ini? Semua riwayat pelunasan terkait juga akan terhapus.">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $loans->links('components.pagination') }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-bank"></i>
        <p>Belum ada data piutang.</p>
    </div>
    @endif
</div>

{{-- Unlinked Repayments --}}
@if(isset($unlinkedRepayments) && $unlinkedRepayments->count())
<div class="card mt-4" style="border-color:rgba(59,130,246,0.3); background:rgba(59,130,246,0.02);">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="card-title text-primary"><i class="bi bi-link-45deg"></i> Pelunasan Bebas (Belum Ditautkan)</div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tgl Pelunasan</th>
                    <th>Nominal Masuk</th>
                    <th>Keterangan</th>
                    <th style="width:140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unlinkedRepayments as $rep)
                <tr>
                    <td>{{ $rep->repayment_date->format('d/m/Y') }}</td>
                    <td class="money-text text-success">+ Rp {{ number_format($rep->amount, 0, ',', '.') }}</td>
                    <td>{{ $rep->notes ?: '—' }}</td>
                    <td>
                        <div class="flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="openLinkRepaymentModal({{ $rep->id }}, {{ $rep->amount }})">
                                <i class="bi bi-link"></i> Tautkan
                            </button>
                            <form action="{{ route('receivable-repayments.destroy', $rep) }}" method="POST" data-confirm="Hapus data pelunasan bebas ini?">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-icon"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Add Loan Modal --}}
<div id="addLoanModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Tambah Piutang Baru</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addLoanModal')">✕</button>
        </div>
        <form action="{{ route('receivables.store') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Nama Peminjam <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Keperluan Piutang <span class="required">*</span></label>
                <input type="text" name="purpose" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Tanggal Piutang <span class="required">*</span></label>
                <input type="date" name="loan_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group mb-4">
                <label>Total Piutang (Rp) <span class="required">*</span></label>
                <input type="number" name="total_amount" class="form-control" required>
            </div>
            <div class="form-group mb-6">
                <label>Keterangan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addLoanModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Piutang</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Loan Modal --}}
<div id="editLoanModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Edit Piutang</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editLoanModal')">✕</button>
        </div>
        <form id="editLoanForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label>Nama Peminjam <span class="required">*</span></label>
                <input type="text" name="name" id="editLoanName" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Keperluan Piutang <span class="required">*</span></label>
                <input type="text" name="purpose" id="editLoanPurpose" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Tanggal Piutang <span class="required">*</span></label>
                <input type="date" name="loan_date" id="editLoanDate" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Total Piutang (Rp) <span class="required">*</span></label>
                <input type="number" name="total_amount" id="editLoanAmount" class="form-control" required>
            </div>
            <div class="form-group mb-6">
                <label>Keterangan Tambahan (Opsional)</label>
                <textarea name="notes" id="editLoanNotes" class="form-control" rows="3"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editLoanModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Global Repayment Modal --}}
<div id="addGlobalRepaymentModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Catat Uang Pelunasan Masuk</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addGlobalRepaymentModal')">✕</button>
        </div>
        <form action="{{ route('receivable-repayments.store') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Pilih Piutang (Opsional)</label>
                <select name="loan_id" class="form-control">
                    <option value="">-- Simpan sebagai uang masuk bebas (Pilih Nanti) --</option>
                    @foreach($activeLoans ?? [] as $activeLoan)
                        <option value="{{ $activeLoan->id }}">{{ $activeLoan->name }} - Sisa: Rp {{ number_format($activeLoan->remaining_amount, 0, ',', '.') }}</option>
                    @endforeach
                </select>
                <span class="form-hint">Anda bisa menyimpan catatan uang masuk ini tanpa menautkannya ke piutang mana pun terlebih dahulu.</span>
            </div>
            <div class="form-group mb-4">
                <label>Tanggal Pelunasan <span class="required">*</span></label>
                <input type="date" name="repayment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group mb-4">
                <label>Nominal Pembayaran (Rp) <span class="required">*</span></label>
                <input type="number" name="amount" class="form-control" required>
            </div>
            <div class="form-group mb-6">
                <label>Keterangan Tambahan</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Transfer BCA"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addGlobalRepaymentModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pelunasan</button>
            </div>
        </form>
    </div>
</div>

{{-- Link Repayment Modal --}}
<div id="linkRepaymentModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Tautkan Uang Pelunasan</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('linkRepaymentModal')">✕</button>
        </div>
        <form id="linkRepaymentForm" method="POST">
            @csrf @method('PATCH')
            <div style="background:rgba(59,130,246,0.05); padding:12px; border-radius:8px; margin-bottom:16px;">
                <div class="text-xs text-muted mb-1">Nominal Uang Masuk:</div>
                <div class="money-text text-success" style="font-size:18px;" id="linkRepaymentAmountDisplay">Rp 0</div>
            </div>
            <div class="form-group mb-6">
                <label>Pilih Piutang <span class="required">*</span></label>
                <select name="loan_id" class="form-control" required>
                    <option value="">-- Pilih Piutang --</option>
                    @foreach($activeLoans ?? [] as $activeLoan)
                        <option value="{{ $activeLoan->id }}">{{ $activeLoan->name }} - Sisa: Rp {{ number_format($activeLoan->remaining_amount, 0, ',', '.') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('linkRepaymentModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Tautkan Sekarang</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.modal-backdrop {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(4, 6, 12, 0.7);
    backdrop-filter: blur(12px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
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
}
@keyframes zoomIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>
@endpush

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('d-none');
}
function closeModal(id) {
    document.getElementById(id).classList.add('d-none');
}
function openEditLoanModal(btn) {
    const form = document.getElementById('editLoanForm');
    form.action = `/receivables/${btn.dataset.id}`;
    document.getElementById('editLoanName').value = btn.dataset.name;
    document.getElementById('editLoanPurpose').value = btn.dataset.purpose;
    document.getElementById('editLoanDate').value = btn.dataset.date;
    document.getElementById('editLoanAmount').value = btn.dataset.amount;
    document.getElementById('editLoanNotes').value = btn.dataset.notes;
    openModal('editLoanModal');
}
function openLinkRepaymentModal(id, amount) {
    const form = document.getElementById('linkRepaymentForm');
    form.action = `/receivable-repayments/${id}/link`;
    document.getElementById('linkRepaymentAmountDisplay').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    openModal('linkRepaymentModal');
}
</script>
@endpush
