<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Dashboard Kost Malang - Manajemen Kamar, Penyewa, dan Keuangan">
    <title>@yield('title', 'Dashboard') — Admin Kost Malang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
</head>
<body>
<div class="layout">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="brand-logo">
                <div class="brand-icon">🏠</div>
                <div class="brand-text">
                    <div class="brand-name">Kost Malang</div>
                    <div class="brand-sub">Admin Panel</div>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Utama</div>

            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i>
                Dashboard
            </a>

            <div class="nav-section-label" style="margin-top:8px;">Kamar & Penyewa</div>

            <a href="{{ route('rooms.index') }}"
               class="nav-item {{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                <i class="bi bi-door-open"></i>
                Database Kamar
            </a>

            <a href="{{ route('tenants.index') }}"
               class="nav-item {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                Data Penyewa
            </a>

            <div class="nav-section-label" style="margin-top:8px;">Keuangan</div>

            <a href="{{ route('payments.index') }}"
               class="nav-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i>
                Penerimaan
            </a>

             <a href="{{ route('other-incomes.index') }}" class="nav-item {{ request()->routeIs('other-incomes.*') ? 'active' : '' }}">
                <i class="bi bi-wallet-fill"></i> Pendapatan Lain-lain
            </a>

            <a href="{{ route('reports.total_omzet') }}" class="nav-item {{ request()->routeIs('reports.total_omzet') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i>
                Laporan Omzet Periode
            </a>


             <a href="{{ route('expenses.index') }}"
               class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i>
                Pengeluaran Kost
            </a>

            <a href="{{ route('maintenances.index') }}"
               class="nav-item {{ request()->routeIs('maintenances.*') ? 'active' : '' }}">
                <i class="bi bi-tools"></i>
                Maintenance Kamar
            </a>

               <a href="{{ route('reports.total_pengeluaran') }}" class="nav-item {{ request()->routeIs('reports.total_pengeluaran') ? 'active' : '' }}">
                <i class="bi bi-graph-down-arrow"></i>
                Laporan Pengeluaran Periode
            </a>


            <a href="{{ route('receivables.index') }}" class="nav-item {{ request()->routeIs('receivables.*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-in-down-left"></i> Piutang Kost
            </a>
            <a href="{{ route('payables.index') }}" class="nav-item {{ request()->routeIs('payables.*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-up-right"></i> Hutang Kost
            </a>

             <a href="{{ route('reports.cash_flow') }}" class="nav-item {{ request()->routeIs('reports.cash_flow') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i>
                Laporan Arus Kas
            </a>
        

            <div class="nav-section-label" style="margin-top:8px;">Lainnya</div>

            <a href="{{ route('lodgings.index') }}"
               class="nav-item {{ request()->routeIs('lodgings.*') ? 'active' : '' }}">
                <i class="bi bi-moon-stars"></i>
                Penginapan
            </a>

            <a href="{{ route('settings.index') }}"
               class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-sliders2"></i>
                Opsi & Pengaturan
            </a>
        </nav>

        {{-- Sidebar Footer --}}
        <div style="padding:16px 20px; border-top:1px solid var(--border-color);">
            <div style="font-size:11px; color:var(--text-muted); text-align:center;">
                Admin Kost v1.0 • {{ date('Y') }}
            </div>
        </div>
    </aside>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="main-content">

        {{-- Topbar --}}
        <header class="topbar">
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                @hasSection('page-subtitle')
                    <div class="topbar-subtitle">@yield('page-subtitle')</div>
                @endif
            </div>
            <div class="topbar-actions">
                <span class="topbar-date">
                    <i class="bi bi-calendar3" style="margin-right:4px;"></i>
                    {{ now()->translatedFormat('l, d-M-Y') }}
                </span>
                @yield('topbar-actions')
            </div>
        </header>

        {{-- Page Content --}}
        <main class="page-content">
            {{-- Print Header (Hidden by default) --}}
            <div class="print-header" style="display:none; text-align:center; margin-bottom:20px; border-bottom:2px solid #000; padding-bottom:10px;">
                <h2 style="margin:0; font-weight:700;">KOST MALANG</h2>
                <h4 style="margin:5px 0;">@yield('page-title', 'Laporan')</h4>
                <p style="margin:0; font-size:12px;">Tanggal Cetak: {{ now()->translatedFormat('d F Y H:i') }}</p>
            </div>
            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="alert alert-success" id="flash-msg">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger" id="flash-msg">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Terdapat kesalahan input:</strong>
                        <ul style="margin:6px 0 0 16px; font-size:12px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
// Auto dismiss flash message
setTimeout(() => {
    const flash = document.getElementById('flash-msg');
    if (flash) {
        flash.style.transition = 'opacity 0.5s';
        flash.style.opacity = '0';
        setTimeout(() => flash.remove(), 500);
    }
}, 4000);

// Facility checkbox visual
document.querySelectorAll('.facility-item input[type="checkbox"]').forEach(cb => {
    const item = cb.closest('.facility-item');
    if (cb.checked) item.classList.add('checked');
    cb.addEventListener('change', () => {
        item.classList.toggle('checked', cb.checked);
    });
});

// Confirm delete with SweetAlert
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: form.dataset.confirm || 'Yakin ingin menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            background: 'var(--bg-card)',
            color: 'var(--text-primary)'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

// Format Rupiah Input Helper
function formatRupiah(angka) {
    // Hanya potong desimal .00 dari database, jangan potong separator ribuan (karena ribuan punya 3 digit misal .000)
    let str_angka = angka.toString().replace(/\.\d{2}$/, ''); 
    let number_string = str_angka.replace(/[^,\d]/g, ''),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
}

// Apply on input event
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('input-rupiah')) {
        e.target.value = formatRupiah(e.target.value);
    }
});

// Remove dots before submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        this.querySelectorAll('.input-rupiah').forEach(input => {
            input.value = input.value.replace(/\./g, '');
        });
    });
});

// Format existing values on load (for edit forms or old input validation)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.input-rupiah').forEach(input => {
        if(input.value) {
            input.value = formatRupiah(input.value);
        }
    });
});
</script>

@if(request('print') === 'all')
<script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            window.print();
        }, 500); // short delay to ensure rendering
    });
</script>
@endif
@stack('modals')
@stack('scripts')
</body>
</html>

