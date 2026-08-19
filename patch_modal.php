<?php
$file = 'resources/views/tenant-deposits/index.blade.php';
$content = file_get_contents($file);

// Find the position of <!-- Modal Tambah Deposit -->
$pos = strpos($content, '<!-- Modal Tambah Deposit -->');
if ($pos !== false) {
    $before = substr($content, 0, $pos);
    
    $modals = <<<BLADE
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
                    @foreach(\$tenants as \$tenant)
                        <option value="{{ \$tenant->id }}">{{ \$tenant->name }} (Kamar {{ \$tenant->room->room_number ?? '-' }})</option>
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
BLADE;

    // Remove the trailing @endsection from original content to append it properly
    $before = preg_replace('/@endsection\s*$/', '', $before);
    
    file_put_contents($file, $before . "\n" . $modals . "\n@endsection\n");
    echo "Modal patched successfully.\n";
} else {
    echo "Modal start tag not found.\n";
}
