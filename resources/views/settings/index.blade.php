@extends('layouts.app')
@section('title', 'Pengaturan Master Data')
@section('page-title', 'Pengaturan Master Data')
@section('page-subtitle', 'Kelola data master dinamis: lantai, tipe kamar, fasilitas, dan kategori keuangan')

@section('content')
<div style="display:grid; grid-template-columns:220px 1fr; gap:24px;">

    {{-- Left Navigation Tabs --}}
    <div style="display:flex; flex-direction:column; gap:6px;">
        <a href="?tab=floors" class="nav-item {{ $activeTab === 'floors' ? 'active' : '' }}" style="border:1px solid var(--border-color); text-decoration:none;">
            <i class="bi bi-layers"></i> Lantai Kost
        </a>
        <a href="?tab=room_types" class="nav-item {{ $activeTab === 'room_types' ? 'active' : '' }}" style="border:1px solid var(--border-color); text-decoration:none;">
            <i class="bi bi-tags"></i> Tipe Kamar
        </a>
        <a href="?tab=facilities" class="nav-item {{ $activeTab === 'facilities' ? 'active' : '' }}" style="border:1px solid var(--border-color); text-decoration:none;">
            <i class="bi bi-check2-square"></i> Fasilitas Kamar
        </a>
        <a href="?tab=maintenance_categories" class="nav-item {{ $activeTab === 'maintenance_categories' ? 'active' : '' }}" style="border:1px solid var(--border-color); text-decoration:none;">
            <i class="bi bi-wrench"></i> Kategori Maintenance
        </a>
        <a href="?tab=expense_categories" class="nav-item {{ $activeTab === 'expense_categories' ? 'active' : '' }}" style="border:1px solid var(--border-color); text-decoration:none;">
            <i class="bi bi-receipt-cutoff"></i> Kategori Pengeluaran
        </a>
        <a href="?tab=tenant_fields" class="nav-item {{ $activeTab === 'tenant_fields' ? 'active' : '' }}" style="border:1px solid var(--border-color); text-decoration:none;">
            <i class="bi bi-person-lines-fill"></i> Field Penyewa
        </a>
        <a href="?tab=lodging" class="nav-item {{ $activeTab === 'lodging' ? 'active' : '' }}" style="border:1px solid var(--border-color); text-decoration:none;">
            <i class="bi bi-moon-stars"></i> Pengaturan Penginapan
        </a>
    </div>

    {{-- Right Configuration Area --}}
    <div>
        {{-- TAB 1: FLOORS --}}
        @if($activeTab === 'floors')
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-layers"></i> Pengaturan Lantai</div>
                <button class="btn btn-primary btn-sm" onclick="openModal('addFloorModal')">
                    <i class="bi bi-plus-lg"></i> Tambah Lantai
                </button>
            </div>
            @if($floors->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>No. Lantai</th><th>Nama Tampilan</th><th style="width:120px;">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($floors as $f)
                        <tr>
                            <td class="fw-600">{{ $f->number }}</td>
                            <td>{{ $f->name }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-warning btn-sm btn-icon" onclick="openEditFloorModal({{ $f->id }}, {{ $f->number }}, '{{ $f->name }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('settings.floors.destroy', $f) }}" method="POST" data-confirm="Hapus lantai {{ $f->name }}? Kamar yang terdaftar di lantai ini tidak akan terhapus tapi lantai referensinya hilang.">
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
            <div class="empty-state">
                <i class="bi bi-layers-half"></i>
                <p>Belum ada konfigurasi lantai.</p>
            </div>
            @endif
        </div>
        @endif

        {{-- TAB 2: ROOM TYPES --}}
        @if($activeTab === 'room_types')
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-tags"></i> Tipe Kamar</div>
                <button class="btn btn-primary btn-sm" onclick="openModal('addTypeModal')">
                    <i class="bi bi-plus-lg"></i> Tambah Tipe
                </button>
            </div>
            @if($roomTypes->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Nama Tipe</th><th>Deskripsi</th><th style="width:120px;">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($roomTypes as $rt)
                        <tr>
                            <td class="fw-600 text-success">{{ $rt->name }}</td>
                            <td>{{ $rt->description ?? '—' }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-warning btn-sm btn-icon" onclick="openEditTypeModal({{ $rt->id }}, '{{ $rt->name }}', '{{ addslashes($rt->description ?? '') }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('settings.room-types.destroy', $rt) }}" method="POST" data-confirm="Hapus tipe kamar {{ $rt->name }}?">
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
            <div class="empty-state">
                <i class="bi bi-tag"></i>
                <p>Belum ada konfigurasi tipe kamar.</p>
            </div>
            @endif
        </div>
        @endif

        {{-- TAB 3: FACILITIES --}}
        @if($activeTab === 'facilities')
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-check2-square"></i> Fasilitas Kamar</div>
                <button class="btn btn-primary btn-sm" onclick="openModal('addFacilityModal')">
                    <i class="bi bi-plus-lg"></i> Tambah Fasilitas
                </button>
            </div>
            @if($facilities->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Nama Fasilitas</th><th>Ikon</th><th>Kategori</th><th style="width:120px;">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($facilities as $fac)
                        <tr>
                            <td class="fw-600">{{ $fac->name }}</td>
                            <td>
                                <span style="background:rgba(255,255,255,0.06); border:1px solid var(--border-color); border-radius:6px; padding:6px 12px; font-size:14px; display:inline-flex; align-items:center; gap:8px;">
                                    <i class="{{ $fac->icon }} text-success"></i> <code>{{ $fac->icon }}</code>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    {{ match($fac->category) {
                                        'furnitur' => '🛋️ Furnitur',
                                        'elektronik' => '⚡ Elektronik',
                                        'kamar_mandi' => '🚿 Kamar Mandi',
                                        'lainnya' => '✨ Lainnya',
                                        default => ucfirst($fac->category),
                                    } }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-warning btn-sm btn-icon" onclick="openEditFacilityModal({{ $fac->id }}, '{{ $fac->name }}', '{{ $fac->category }}', '{{ $fac->icon }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('settings.facilities.destroy', $fac) }}" method="POST" data-confirm="Hapus fasilitas {{ $fac->name }}?">
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
            <div class="empty-state">
                <i class="bi bi-check-square-fill"></i>
                <p>Belum ada konfigurasi fasilitas.</p>
            </div>
            @endif
        </div>
        @endif

        {{-- TAB 4: MAINTENANCE CATEGORIES --}}
        @if($activeTab === 'maintenance_categories')
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-wrench"></i> Kategori Perawatan / Maintenance</div>
                <button class="btn btn-primary btn-sm" onclick="openModal('addMaintCatModal')">
                    <i class="bi bi-plus-lg"></i> Tambah Kategori
                </button>
            </div>
            @if($maintenanceCategories->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Nama Kategori</th><th>Slug Referensi</th><th style="width:120px;">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($maintenanceCategories as $mc)
                        <tr>
                            <td class="fw-600 text-warning">{{ $mc->name }}</td>
                            <td><code>{{ $mc->slug }}</code></td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-warning btn-sm btn-icon" onclick="openEditMaintCatModal({{ $mc->id }}, '{{ $mc->name }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('settings.maintenance-categories.destroy', $mc) }}" method="POST" data-confirm="Hapus kategori perawatan {{ $mc->name }}? Rekaman perawatan sebelumnya akan kehilangan referensi.">
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
            <div class="empty-state">
                <i class="bi bi-wrench-adjustable"></i>
                <p>Belum ada kategori perawatan.</p>
            </div>
            @endif
        </div>
        @endif

        {{-- TAB 5: EXPENSE CATEGORIES --}}
        @if($activeTab === 'expense_categories')
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-receipt-cutoff"></i> Kategori Pengeluaran Kost</div>
                <button class="btn btn-primary btn-sm" onclick="openModal('addExpCatModal')">
                    <i class="bi bi-plus-lg"></i> Tambah Kategori
                </button>
            </div>
            @if($expenseCategories->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Nama Kategori</th><th>Ikon</th><th>Slug Referensi</th><th style="width:120px;">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($expenseCategories as $ec)
                        <tr>
                            <td class="fw-600 text-danger">{{ $ec->name }}</td>
                            <td>
                                <span style="background:rgba(255,255,255,0.06); border:1px solid var(--border-color); border-radius:6px; padding:6px 12px; font-size:14px; display:inline-flex; align-items:center; gap:8px;">
                                    <i class="{{ $ec->icon }} text-danger"></i> <code>{{ $ec->icon }}</code>
                                </span>
                            </td>
                            <td><code>{{ $ec->slug }}</code></td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-warning btn-sm btn-icon" onclick="openEditExpCatModal({{ $ec->id }}, '{{ $ec->name }}', '{{ $ec->icon }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('settings.expense-categories.destroy', $ec) }}" method="POST" data-confirm="Hapus kategori pengeluaran {{ $ec->name }}? Data pengeluaran terkait akan kehilangan referensi.">
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
            <div class="empty-state">
                <i class="bi bi-receipt"></i>
                <p>Belum ada kategori pengeluaran.</p>
            </div>
            @endif
        </div>
        @endif

        {{-- TAB: TENANT FIELDS --}}
        @if($activeTab === 'tenant_fields')
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-person-lines-fill"></i> Field Tambahan Penyewa</div>
                <button class="btn btn-primary btn-sm" onclick="openModal('addTenantFieldModal')">
                    <i class="bi bi-plus-lg"></i> Tambah Field
                </button>
            </div>
            <div style="background:rgba(59,130,246,0.06); border:1px solid rgba(59,130,246,0.2); border-radius:8px; padding:12px 14px; margin-bottom:16px;">
                <div style="font-size:13px; color:var(--text-secondary);">
                    <i class="bi bi-info-circle" style="color:#3b82f6;"></i>
                    &nbsp;Field di sini akan muncul di formulir tambah/edit penyewa. Gunakan ini untuk menambah kolom kustom seperti "Nama Panggilan", "Universitas", "Jurusan", dll.
                </div>
            </div>
            @if($tenantCustomFields->count())
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr><th>Nama Field</th><th>Kunci (Key)</th><th>Tipe</th><th>Wajib?</th><th>Urutan</th><th style="width:120px;">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($tenantCustomFields as $tf)
                        <tr>
                            <td class="fw-600">{{ $tf->name }}</td>
                            <td><code style="font-size:12px; background:rgba(255,255,255,0.05); padding:2px 6px; border-radius:4px;">{{ $tf->field_key }}</code></td>
                            <td><span class="badge badge-secondary">{{ ucfirst($tf->type) }}</span></td>
                            <td>
                                @if($tf->is_required)
                                    <span class="badge badge-danger">Wajib</span>
                                @else
                                    <span class="text-muted text-sm">Opsional</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $tf->sort_order }}</td>
                            <td>
                                <div class="flex gap-2">
                                    <button class="btn btn-warning btn-sm btn-icon"
                                        onclick="openEditTenantFieldModal({{ $tf->id }}, '{{ addslashes($tf->name) }}', '{{ $tf->type }}', {{ $tf->is_required ? 1 : 0 }}, {{ $tf->sort_order }}, '{{ addslashes($tf->placeholder ?? '') }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('settings.tenant-fields.destroy', $tf) }}" method="POST"
                                        data-confirm="Hapus field '{{ $tf->name }}'? Data yang sudah diisi penyewa akan ikut terhapus.">
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
            <div class="empty-state">
                <i class="bi bi-person-lines-fill"></i>
                <p>Belum ada field tambahan penyewa.</p>
                <button class="btn btn-primary btn-sm" onclick="openModal('addTenantFieldModal')">+ Tambah Field Pertama</button>
            </div>
            @endif
        </div>
        @endif

        {{-- TAB: LODGING SETTINGS --}}
        @if($activeTab === 'lodging')
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="bi bi-moon-stars"></i> Pengaturan Harga Penginapan</div>
            </div>
            <div style="max-width:500px;">
                <form action="{{ route('settings.lodging-price.update') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label>Harga Default per Malam (Rp)</label>
                        <input type="text" inputmode="numeric" name="lodging_default_price" class="form-control input-rupiah"
                            value="{{ $lodgingDefaultPrice }}" min="0" required>
                        <span class="form-hint">Nilai ini akan menjadi harga pre-fill saat membuat data penginapan baru. Harga tetap bisa diubah per transaksi.</span>
                    </div>
                    <div style="margin-top:16px;">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Simpan Harga Default</button>
                    </div>
                </form>
                <hr class="section-divider" style="margin:24px 0;">
                <div style="background:rgba(0,212,170,0.05); border:1px solid rgba(0,212,170,0.15); border-radius:10px; padding:14px 16px;">
                    <div style="font-weight:600; margin-bottom:4px; color:var(--text-primary);">Harga Saat Ini</div>
                    <div class="money-text" style="font-size:24px; color:var(--accent-primary);">Rp {{ number_format($lodgingDefaultPrice, 0, ',', '.') }}</div>
                    <div class="text-muted text-sm" style="margin-top:4px;">per malam (default)</div>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

{{-- ==================== MODALS LIST ==================== --}}

{{-- Floor Add Modal --}}
<div id="addFloorModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Tambah Lantai</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addFloorModal')">✕</button>
        </div>
        <form action="{{ route('settings.floors.store') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Nomor Lantai <span class="required">*</span></label>
                <input type="number" name="number" class="form-control" placeholder="Contoh: 1, 2, 3" required>
            </div>
            <div class="form-group mb-6">
                <label>Nama Lantai <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Lantai Dasar, Lantai 1" required>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addFloorModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Floor Edit Modal --}}
<div id="editFloorModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Edit Lantai</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editFloorModal')">✕</button>
        </div>
        <form id="editFloorForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label>Nomor Lantai <span class="required">*</span></label>
                <input type="number" name="number" id="editFloorNumber" class="form-control" required>
            </div>
            <div class="form-group mb-6">
                <label>Nama Lantai <span class="required">*</span></label>
                <input type="text" name="name" id="editFloorName" class="form-control" required>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editFloorModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Room Type Add Modal --}}
<div id="addTypeModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Tambah Tipe Kamar</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addTypeModal')">✕</button>
        </div>
        <form action="{{ route('settings.room-types.store') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Nama Tipe <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Premium Suite" required>
            </div>
            <div class="form-group mb-6">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Fasilitas dan kelengkapan tipe..."></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTypeModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Room Type Edit Modal --}}
<div id="editTypeModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Edit Tipe Kamar</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editTypeModal')">✕</button>
        </div>
        <form id="editTypeForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label>Nama Tipe <span class="required">*</span></label>
                <input type="text" name="name" id="editTypeName" class="form-control" required>
            </div>
            <div class="form-group mb-6">
                <label>Deskripsi</label>
                <textarea name="description" id="editTypeDesc" class="form-control" rows="3"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editTypeModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Facility Add Modal --}}
<div id="addFacilityModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Tambah Fasilitas</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addFacilityModal')">✕</button>
        </div>
        <form action="{{ route('settings.facilities.store') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Nama Fasilitas <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Water Heater" required>
            </div>
            <div class="form-group mb-4">
                <label>Kategori <span class="required">*</span></label>
                <select name="category" class="form-control" required>
                    <option value="furnitur">🛋️ Furnitur</option>
                    <option value="elektronik">⚡ Elektronik</option>
                    <option value="kamar_mandi">🚿 Kamar Mandi</option>
                    <option value="lainnya">✨ Lainnya</option>
                </select>
            </div>
            <div class="form-group mb-6">
                <label>Ikon Bootstrap</label>
                <input type="text" name="icon" class="form-control" placeholder="Contoh: bi-thermometer-sun">
                <span class="form-hint">Gunakan referensi dari <a href="https://icons.getbootstrap.com/" target="_blank" style="color:var(--accent-primary);">Bootstrap Icons</a></span>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addFacilityModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Facility Edit Modal --}}
<div id="editFacilityModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Edit Fasilitas</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editFacilityModal')">✕</button>
        </div>
        <form id="editFacilityForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label>Nama Fasilitas <span class="required">*</span></label>
                <input type="text" name="name" id="editFacName" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Kategori <span class="required">*</span></label>
                <select name="category" id="editFacCategory" class="form-control" required>
                    <option value="furnitur">🛋️ Furnitur</option>
                    <option value="elektronik">⚡ Elektronik</option>
                    <option value="kamar_mandi">🚿 Kamar Mandi</option>
                    <option value="lainnya">✨ Lainnya</option>
                </select>
            </div>
            <div class="form-group mb-6">
                <label>Ikon Bootstrap</label>
                <input type="text" name="icon" id="editFacIcon" class="form-control">
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editFacilityModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Maint Category Add Modal --}}
<div id="addMaintCatModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Tambah Kategori Perawatan</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addMaintCatModal')">✕</button>
        </div>
        <form action="{{ route('settings.maintenance-categories.store') }}" method="POST">
            @csrf
            <div class="form-group mb-6">
                <label>Nama Kategori <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Plumbing (Pipa/Air)" required>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addMaintCatModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Maint Category Edit Modal --}}
<div id="editMaintCatModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Edit Kategori Perawatan</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editMaintCatModal')">✕</button>
        </div>
        <form id="editMaintCatForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-6">
                <label>Nama Kategori <span class="required">*</span></label>
                <input type="text" name="name" id="editMaintCatName" class="form-control" required>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editMaintCatModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Expense Category Add Modal --}}
<div id="addExpCatModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Tambah Kategori Pengeluaran</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addExpCatModal')">✕</button>
        </div>
        <form action="{{ route('settings.expense-categories.store') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Nama Kategori <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Internet / WiFi" required>
            </div>
            <div class="form-group mb-6">
                <label>Ikon Bootstrap</label>
                <input type="text" name="icon" class="form-control" placeholder="Contoh: bi-wifi">
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addExpCatModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Expense Category Edit Modal --}}
<div id="editExpCatModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Edit Kategori Pengeluaran</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editExpCatModal')">✕</button>
        </div>
        <form id="editExpCatForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label>Nama Kategori <span class="required">*</span></label>
                <input type="text" name="name" id="editExpCatName" class="form-control" required>
            </div>
            <div class="form-group mb-6">
                <label>Ikon Bootstrap</label>
                <input type="text" name="icon" id="editExpCatIcon" class="form-control">
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editExpCatModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Add Tenant Field Modal --}}
<div id="addTenantFieldModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Tambah Field Penyewa</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('addTenantFieldModal')">✕</button>
        </div>
        <form action="{{ route('settings.tenant-fields.store') }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label>Nama Field <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Nama Panggilan" required>
            </div>
            <div class="form-group mb-4">
                <label>Tipe Input <span class="required">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="text">Text (satu baris)</option>
                    <option value="number">Number (angka)</option>
                    <option value="date">Date (tanggal)</option>
                    <option value="textarea">Textarea (banyak baris)</option>
                </select>
            </div>
            <div class="form-group mb-4">
                <label>Placeholder</label>
                <input type="text" name="placeholder" class="form-control" placeholder="Teks petunjuk di dalam kolom">
            </div>
            <div style="display:flex; gap:16px; margin-bottom:16px;">
                <div class="form-group" style="flex:1;">
                    <label>Urutan tampil</label>
                    <input type="number" name="sort_order" class="form-control" value="0" min="0">
                </div>
                <div style="display:flex; align-items:flex-end; padding-bottom:4px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:0;">
                        <input type="checkbox" name="is_required" value="1" style="width:16px; height:16px;">
                        <span>Field Wajib?</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTenantFieldModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Field</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Tenant Field Modal --}}
<div id="editTenantFieldModal" class="modal-backdrop d-none">
    <div class="modal-card">
        <div class="card-header">
            <div class="card-title">Edit Field Penyewa</div>
            <button class="btn btn-secondary btn-sm btn-icon" onclick="closeModal('editTenantFieldModal')">✕</button>
        </div>
        <form id="editTenantFieldForm" method="POST">
            @csrf @method('PUT')
            <div class="form-group mb-4">
                <label>Nama Field <span class="required">*</span></label>
                <input type="text" name="name" id="editTenantFieldName" class="form-control" required>
            </div>
            <div class="form-group mb-4">
                <label>Tipe Input <span class="required">*</span></label>
                <select name="type" id="editTenantFieldType" class="form-control" required>
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="date">Date</option>
                    <option value="textarea">Textarea</option>
                </select>
            </div>
            <div class="form-group mb-4">
                <label>Placeholder</label>
                <input type="text" name="placeholder" id="editTenantFieldPlaceholder" class="form-control">
            </div>
            <div style="display:flex; gap:16px; margin-bottom:16px;">
                <div class="form-group" style="flex:1;">
                    <label>Urutan tampil</label>
                    <input type="number" name="sort_order" id="editTenantFieldSort" class="form-control" min="0">
                </div>
                <div style="display:flex; align-items:flex-end; padding-bottom:4px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin-bottom:0;">
                        <input type="checkbox" name="is_required" id="editTenantFieldRequired" value="1" style="width:16px; height:16px;">
                        <span>Field Wajib?</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editTenantFieldModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Modal CSS system inline for configuration convenience */
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
    max-width: 460px;
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

// Edit Floor Populator
function openEditFloorModal(id, number, name) {
    const form = document.getElementById('editFloorForm');
    form.action = `/settings/floors/${id}`;
    document.getElementById('editFloorNumber').value = number;
    document.getElementById('editFloorName').value = name;
    openModal('editFloorModal');
}

// Edit Room Type Populator
function openEditTypeModal(id, name, desc) {
    const form = document.getElementById('editTypeForm');
    form.action = `/settings/room-types/${id}`;
    document.getElementById('editTypeName').value = name;
    document.getElementById('editTypeDesc').value = desc;
    openModal('editTypeModal');
}

// Edit Facility Populator
function openEditFacilityModal(id, name, cat, icon) {
    const form = document.getElementById('editFacilityForm');
    form.action = `/settings/facilities/${id}`;
    document.getElementById('editFacName').value = name;
    document.getElementById('editFacCategory').value = cat;
    document.getElementById('editFacIcon').value = icon;
    openModal('editFacilityModal');
}

// Edit Maintenance Category Populator
function openEditMaintCatModal(id, name) {
    const form = document.getElementById('editMaintCatForm');
    form.action = `/settings/maintenance-categories/${id}`;
    document.getElementById('editMaintCatName').value = name;
    openModal('editMaintCatModal');
}

// Edit Expense Category Populator
function openEditExpCatModal(id, name, icon) {
    const form = document.getElementById('editExpCatForm');
    form.action = `/settings/expense-categories/${id}`;
    document.getElementById('editExpCatName').value = name;
    document.getElementById('editExpCatIcon').value = icon;
    openModal('editExpCatModal');
}

// Edit Tenant Field Populator
function openEditTenantFieldModal(id, name, type, isRequired, sortOrder, placeholder) {
    const form = document.getElementById('editTenantFieldForm');
    form.action = `/settings/tenant-fields/${id}`;
    document.getElementById('editTenantFieldName').value = name;
    document.getElementById('editTenantFieldType').value = type;
    document.getElementById('editTenantFieldRequired').checked = isRequired == 1;
    document.getElementById('editTenantFieldSort').value = sortOrder;
    document.getElementById('editTenantFieldPlaceholder').value = placeholder || '';
    openModal('editTenantFieldModal');
}
</script>
@endpush

{{-- ===== TAB: TENANT FIELDS ===== (inserted inline via PHP) --}}
@once
@push('tab_tenant_fields')
@endpush
@endonce
