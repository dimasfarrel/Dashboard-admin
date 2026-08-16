@extends('layouts.app')
@section('title', 'Detail Penyewa — ' . $tenant->name)
@section('page-title', $tenant->name)
@section('page-subtitle', 'Detail data penyewa dan riwayat pembayaran')

@section('topbar-actions')
    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-warning btn-sm">
        <i class="bi bi-pencil"></i> Edit
    </a>
    <a href="{{ route('tenants.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('tenants.index') }}">Penyewa</a>
    <span class="separator">/</span>
    <span class="current">{{ $tenant->name }}</span>
</div>

<div style="display:grid; grid-template-columns:1fr 300px; gap:24px;">
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Data Diri --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-person-badge"></i> Data Diri</div>
                @if($tenant->status === 'active')
                <span class="badge badge-success"><i class="bi bi-circle-fill" style="font-size:7px;"></i> Aktif</span>
                @else
                <span class="badge badge-secondary">Tidak Aktif</span>
                @endif
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama Lengkap</span>
                    <span class="detail-value">
                        {{ $tenant->name }}
                        @if($tenant->nickname) <span style="font-weight:400; color:var(--text-secondary); font-size:13px;">("{{ $tenant->nickname }}")</span> @endif
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nomor Identitas</span>
                    <span class="detail-value" style="font-family:monospace; letter-spacing:1px;">{{ $tenant->nik }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">WhatsApp</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tenant->phone_wa) }}" target="_blank"
                       style="color:#25D366; text-decoration:none; font-weight:600; font-size:15px;">
                        <i class="bi bi-whatsapp"></i> {{ $tenant->phone_wa }}
                    </a>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Jenis Kelamin</span>
                    <span class="detail-value">{{ $tenant->gender ? ucfirst($tenant->gender) : '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Lahir</span>
                    <span class="detail-value">{{ $tenant->birth_date?->translatedFormat('d-M-Y') ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Pekerjaan</span>
                    <span class="detail-value">{{ $tenant->occupation ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Alamat Lengkap</span>
                    <span class="detail-value">{{ $tenant->origin_city ?: '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Kamar</span>
                    @if($tenant->room)
                    <a href="{{ route('rooms.show', $tenant->room) }}" style="color:var(--accent-primary); text-decoration:none; font-weight:600;">
                        <i class="bi bi-door-open"></i> Kamar {{ $tenant->room->room_number }}
                    </a>
                    @else
                    <span class="detail-value">—</span>
                    @endif
                </div>
                <div class="detail-item">
                    <span class="detail-label">Mulai Sewa</span>
                    <span class="detail-value">{{ $tenant->start_date->translatedFormat('d-M-Y') }}</span>
                </div>
                @if($tenant->end_date)
                <div class="detail-item">
                    <span class="detail-label">Kontrak Berakhir</span>
                    <span class="detail-value {{ $tenant->end_date->isPast() ? 'text-danger' : '' }}">
                        {{ $tenant->end_date->translatedFormat('d-M-Y') }}
                        @if($tenant->end_date->isPast())
                        <span class="badge badge-danger" style="margin-left:6px;">Kadaluarsa</span>
                        @endif
                    </span>
                </div>
                @endif
            </div>

            @if($customFields->count())
            <hr class="section-divider">
            <div class="card-title mb-4" style="font-size:13px;"><i class="bi bi-sliders" style="color:var(--accent-primary);"></i> Informasi Tambahan</div>
            <div class="detail-grid">
                @foreach($customFields as $field)
                <div class="detail-item {{ $field->type === 'textarea' ? 'span-full' : '' }}">
                    <span class="detail-label">{{ $field->name }}</span>
                    <span class="detail-value">{{ $tenant->getCustomFieldValue($field->field_key) ?: '—' }}</span>
                </div>
                @endforeach
            </div>
            @endif

            @if($tenant->emergency_contact_name || $tenant->emergency_contact_phone)
            <hr class="section-divider">
            <div class="card-title mb-4" style="font-size:13px;"><i class="bi bi-telephone-fill" style="color:var(--accent-orange);"></i> Kontak Darurat</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nama</span>
                    <span class="detail-value">{{ $tenant->emergency_contact_name ?? '—' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nomor HP</span>
                    @if($tenant->emergency_contact_phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tenant->emergency_contact_phone) }}" target="_blank"
                       style="color:#25D366; text-decoration:none;">
                        <i class="bi bi-whatsapp"></i> {{ $tenant->emergency_contact_phone }}
                    </a>
                    @else
                    <span class="detail-value">—</span>
                    @endif
                </div>
            </div>
            @endif

            @if($tenant->notes)
            <hr class="section-divider">
            <div class="detail-label" style="margin-bottom:6px;">Catatan</div>
            <div style="color:var(--text-secondary);">{{ $tenant->notes }}</div>
            @endif
        </div>

        {{-- Riwayat Pembayaran --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-receipt"></i> Riwayat Pembayaran</div>
                <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">+ Catat</a>
            </div>
            @if($tenant->payments->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Periode</th><th>Nominal</th><th>Metode</th><th>Dibayar</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($tenant->payments as $pay)
                        <tr>
                            <td class="fw-600">{{ $pay->period_label }}</td>
                            <td class="money-text">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($pay->payment_method ?? '—') }}</td>
                            <td class="text-sm">{{ $pay->paid_at?->translatedFormat('d-M-Y') ?? '—' }}</td>
                            <td>
                                @if($pay->status === 'paid')     <span class="badge badge-success">Lunas</span>
                                @elseif($pay->status === 'overdue') <span class="badge badge-danger">Terlambat</span>
                                @else                               <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('payments.edit', $pay) }}" class="btn btn-secondary btn-sm btn-icon">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding-top:16px; border-top:1px solid var(--border-color); margin-top:16px;">
                <span class="text-muted text-sm">Total dibayar: </span>
                <span class="fw-700 money-text" style="color:var(--accent-primary);">
                    Rp {{ number_format($tenant->payments->where('status','paid')->sum('amount'), 0, ',', '.') }}
                </span>
            </div>
            @else
            <div class="text-muted text-sm">Belum ada riwayat pembayaran.</div>
            @endif
        </div>

        {{-- Deposit Jaminan --}}
        <div class="card" style="border-color:rgba(168,85,247,0.25);">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-safe2" style="color:#a855f7;"></i> Deposit Jaminan</div>
                <div class="flex gap-2">
                    <button type="button" class="btn btn-sm" style="background:rgba(168,85,247,0.12);color:#a855f7;border:1px solid rgba(168,85,247,0.3);" onclick="openModal('addDepositModal')">
                        <i class="bi bi-plus-circle"></i> Setor
                    </button>
                    @if($tenant->deposit_balance > 0)
                    <button type="button" class="btn btn-sm" style="background:rgba(239,68,68,0.10);color:#ef4444;border:1px solid rgba(239,68,68,0.25);" onclick="openModal('deductDepositModal')">
                        <i class="bi bi-dash-circle"></i> Kurangi
                    </button>
                    @endif
                </div>
            </div>

            {{-- Ringkasan saldo --}}
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px;">
                <div style="background:rgba(168,85,247,0.06); border:1px solid rgba(168,85,247,0.2); border-radius:10px; padding:14px; text-align:center;">
                    <div style="font-size:11px; color:var(--text-muted); margin-bottom:4px;">Saldo Deposit</div>
                    <div class="money-text fw-700" style="font-size:15px; color:#a855f7;">Rp {{ number_format($tenant->deposit_balance, 0, ',', '.') }}</div>
                </div>
                <div style="background:rgba(0,212,170,0.06); border:1px solid rgba(0,212,170,0.2); border-radius:10px; padding:14px; text-align:center;">
                    <div style="font-size:11px; color:var(--text-muted); margin-bottom:4px;">Total Setor</div>
                    <div class="money-text fw-600" style="font-size:14px; color:#00d4aa;">Rp {{ number_format($tenant->deposit_total_credit, 0, ',', '.') }}</div>
                </div>
                <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.18); border-radius:10px; padding:14px; text-align:center;">
                    <div style="font-size:11px; color:var(--text-muted); margin-bottom:4px;">Total Potongan</div>
                    <div class="money-text fw-600" style="font-size:14px; color:#ef4444;">Rp {{ number_format($tenant->deposit_total_debit, 0, ',', '.') }}</div>
                </div>
            </div>

            @if($tenant->deposits->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                            <th style="width:50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenant->deposits as $dep)
                        <tr>
                            <td class="text-sm">{{ $dep->date->translatedFormat('d-M-Y') }}</td>
                            <td>
                                @if($dep->type === 'credit')
                                    <span class="badge" style="background:rgba(0,212,170,0.15);color:#00d4aa;border:1px solid rgba(0,212,170,0.3);"><i class="bi bi-arrow-down-circle"></i> Setor</span>
                                @else
                                    <span class="badge" style="background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.25);"><i class="bi bi-arrow-up-circle"></i> Potongan</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight:500;">{{ $dep->description }}</div>
                                @if($dep->notes)<div class="text-muted text-sm">{{ $dep->notes }}</div>@endif
                            </td>
                            <td class="money-text fw-600" style="color:{{ $dep->type === 'credit' ? '#00d4aa' : '#ef4444' }};">
                                {{ $dep->type === 'credit' ? '+' : '-' }} Rp {{ number_format($dep->amount, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-warning btn-sm btn-icon" onclick="editDeposit({{ $dep->id }}, '{{ $dep->date->format('Y-m-d') }}', '{{ $dep->description }}', '{{ $dep->amount }}', '{{ $dep->notes }}')"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('tenant-deposits.destroy', $dep) }}" method="POST" data-confirm="Hapus transaksi deposit ini?">
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
            @else
            <div class="empty-state" style="padding:24px 0;">
                <i class="bi bi-safe2" style="font-size:32px; color:rgba(168,85,247,0.4);"></i>
                <p style="margin-top:8px;">Belum ada transaksi deposit.</p>
                <button type="button" class="btn btn-sm" style="background:rgba(168,85,247,0.12);color:#a855f7;border:1px solid rgba(168,85,247,0.3);margin-top:8px;" onclick="openModal('addDepositModal')">
                    <i class="bi bi-plus-circle"></i> Catat Deposit Pertama
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- RIGHT: Dokumen + Aksi --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        @if($tenant->ktp_photo || $tenant->selfie_photo)
        <div class="card">
            <div class="card-title mb-4"><i class="bi bi-images"></i> Dokumen</div>
            @if($tenant->ktp_photo)
            <div style="margin-bottom:12px;">
                <div class="detail-label" style="margin-bottom:6px;">📋 Foto KTP</div>
                <a href="{{ asset('storage/' . $tenant->ktp_photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $tenant->ktp_photo) }}" style="width:100%; height:140px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                </a>
            </div>
            @endif
            @if($tenant->selfie_photo)
            <div>
                <div class="detail-label" style="margin-bottom:6px;">🤳 Selfie + KTP</div>
                <a href="{{ asset('storage/' . $tenant->selfie_photo) }}" target="_blank">
                    <img src="{{ asset('storage/' . $tenant->selfie_photo) }}" style="width:100%; height:140px; object-fit:cover; border-radius:8px; border:1px solid var(--border-color);">
                </a>
            </div>
            @endif
        </div>
        @endif

        {{-- Kartu Saldo Deposit (mini di sidebar) --}}
        <div class="card" style="border-color:rgba(168,85,247,0.25); background:linear-gradient(135deg, rgba(168,85,247,0.05) 0%, transparent 100%);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(168,85,247,0.15);display:flex;align-items:center;justify-content:center;color:#a855f7;font-size:18px;">
                    <i class="bi bi-safe2"></i>
                </div>
                <div>
                    <div style="font-size:11px; color:var(--text-muted);">Saldo Deposit</div>
                    <div class="money-text fw-700" style="font-size:18px; color:#a855f7;">Rp {{ number_format($tenant->deposit_balance, 0, ',', '.') }}</div>
                </div>
            </div>
            <div style="display:flex; gap:6px;">
                <button type="button" class="btn btn-sm" style="flex:1; justify-content:center; background:rgba(168,85,247,0.12);color:#a855f7;border:1px solid rgba(168,85,247,0.3);" onclick="openModal('addDepositModal')">
                    <i class="bi bi-plus-circle"></i> Setor
                </button>
                @if($tenant->deposit_balance > 0)
                <button type="button" class="btn btn-sm" style="flex:1; justify-content:center; background:rgba(239,68,68,0.10);color:#ef4444;border:1px solid rgba(239,68,68,0.25);" onclick="openModal('deductDepositModal')">
                    <i class="bi bi-dash-circle"></i> Kurangi
                </button>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-title mb-4"><i class="bi bi-lightning"></i> Aksi</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-warning" style="justify-content:center;">
                    <i class="bi bi-pencil"></i> Edit Data
                </a>
                <a href="{{ route('payments.create') }}" class="btn btn-secondary" style="justify-content:center;">
                    <i class="bi bi-cash-coin"></i> Catat Pembayaran
                </a>
                <hr class="section-divider">
                <form action="{{ route('tenants.destroy', $tenant) }}" method="POST"
                    data-confirm="Hapus data penyewa {{ $tenant->name }}? Riwayat pembayaran juga akan terhapus.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;">
                        <i class="bi bi-trash"></i> Hapus Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Modal: Tambah Deposit (Setor) --}}
@push('modals')
<div id="addDepositModal" class="modal-backdrop d-none">
    <div class="modal-card" style="max-width:460px;">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-safe2" style="color:#a855f7;"></i> Tambah Deposit Jaminan</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addDepositModal')">✕</button>
        </div>
        <div style="background:rgba(168,85,247,0.06); border:1px solid rgba(168,85,247,0.2); border-radius:10px; padding:12px 14px; margin-bottom:20px;">
            <div style="font-size:11px; color:var(--text-muted);">Saldo Deposit Saat Ini</div>
            <div class="money-text fw-700" style="font-size:20px; color:#a855f7;">Rp {{ number_format($tenant->deposit_balance, 0, ',', '.') }}</div>
        </div>
        <form action="{{ route('tenant-deposits.store', $tenant) }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Nominal Deposit (Rp) <span class="required">*</span></label>
                <input type="text" inputmode="numeric" name="amount" class="form-control input-rupiah" placeholder="Contoh: 500.000" required>
            </div>
            <div class="form-group mb-4">
                <label>Keterangan <span class="required">*</span></label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Deposit awal masuk, Pembayaran jaminan" required>
            </div>
            <div class="form-group mb-4">
                <label>Tanggal <span class="required">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group mb-6">
                <label>Catatan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addDepositModal')">Batal</button>
                <button type="submit" class="btn" style="background:rgba(168,85,247,0.9);color:white;border:none;"><i class="bi bi-safe2"></i> Simpan Deposit</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Kurangi Deposit --}}
<div id="deductDepositModal" class="modal-backdrop d-none">
    <div class="modal-card" style="max-width:460px;">
        <div class="card-header">
            <div class="card-title"><i class="bi bi-dash-circle" style="color:#ef4444;"></i> Kurangi Deposit</div>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('deductDepositModal')">✕</button>
        </div>
        <div style="background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.2); border-radius:10px; padding:12px 14px; margin-bottom:20px;">
            <div style="font-size:11px; color:var(--text-muted);">Saldo Deposit Tersedia</div>
            <div class="money-text fw-700" style="font-size:20px; color:#ef4444;">Rp {{ number_format($tenant->deposit_balance, 0, ',', '.') }}</div>
        </div>
        @if(session('deduct_error')) 
        <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:10px 14px;margin-bottom:16px;color:#ef4444;font-size:13px;">
            <i class="bi bi-exclamation-triangle"></i> {{ session('deduct_error') }}
        </div> 
        @endif
        <form action="{{ route('tenant-deposits.deduct', $tenant) }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Jumlah Pengurangan (Rp) <span class="required">*</span></label>
                <input type="text" inputmode="numeric" name="amount" class="form-control input-rupiah" placeholder="Maks: Rp {{ number_format($tenant->deposit_balance, 0, ',', '.') }}" required>
                <span class="form-hint">Tidak bisa melebihi saldo deposit yang tersedia.</span>
            </div>
            <div class="form-group mb-4">
                <label>Keperluan / Keterangan <span class="required">*</span></label>
                <input type="text" name="description" class="form-control" placeholder="Contoh: Penggantian AC rusak, Kunci hilang" required>
            </div>
            <div class="form-group mb-4">
                <label>Tanggal <span class="required">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group mb-6">
                <label>Catatan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Detail kerusakan atau catatan lainnya..."></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deductDepositModal')">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="bi bi-dash-circle"></i> Kurangi Deposit</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Deposit Modal --}}
<div id="editDepositModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="flex justify-between items-center mb-6">
            <h3 class="fw-700 m-0" style="font-size:18px;">Edit Transaksi Deposit</h3>
            <button class="btn btn-icon" style="background:transparent;" onclick="closeModal('editDepositModal')">
                <i class="bi bi-x-lg text-muted"></i>
            </button>
        </div>
        <form id="editDepositForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label>Nominal (Rp) <span class="required">*</span></label>
                <input type="text" inputmode="numeric" name="amount" id="edit_deposit_amount" class="form-control input-rupiah" required>
            </div>
            <div class="form-group mb-4">
                <label>Keperluan / Keterangan <span class="required">*</span></label>
                <input type="text" name="description" id="edit_deposit_desc" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Tanggal <span class="required">*</span></label>
                <input type="date" name="date" id="edit_deposit_date" class="form-control" required>
            </div>
            <div class="form-group mb-6">
                <label>Catatan (Opsional)</label>
                <textarea name="notes" id="edit_deposit_notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editDepositModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('styles')
<style>
.modal-backdrop {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(4, 6, 12, 0.72);
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
    to   { opacity: 1; transform: scale(1); }
}
</style>
@endpush

@push('scripts')
<script>
function openModal(id)  { document.getElementById(id).classList.remove('d-none'); }
function closeModal(id) { document.getElementById(id).classList.add('d-none'); }

function editDeposit(id, date, desc, amount, notes) {
    document.getElementById('editDepositForm').action = `/tenant-deposits/${id}`;
    document.getElementById('edit_deposit_date').value = date;
    document.getElementById('edit_deposit_desc').value = desc;
    document.getElementById('edit_deposit_amount').value = formatRupiah(amount);
    document.getElementById('edit_deposit_notes').value = notes;
    openModal('editDepositModal');
}

// Auto buka modal kurangi jika ada error
@if(session('deduct_error'))
document.addEventListener('DOMContentLoaded', () => openModal('deductDepositModal'));
@endif
</script>
@endpush
