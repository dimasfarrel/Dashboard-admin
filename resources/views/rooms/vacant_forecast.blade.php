@extends('layouts.app')
@section('title', 'Perkiraan Kamar Kosong')
@section('page-title', 'Perkiraan Kamar Kosong')
@section('page-subtitle', 'Laporan prediksi kamar kosong berdasarkan masa berakhir kontrak penyewa')

@section('content')
<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-filter"></i> Filter Periode</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('rooms.vacant-forecast') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block;">Bulan Kosong</label>
                <select name="month" class="form-control" style="width: 150px;">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::now()->setMonth($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px; display: block;">Tahun</label>
                <input type="number" name="year" class="form-control" style="width: 100px;" value="{{ request('year', date('Y')) }}">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Tampilkan</button>
                <a href="{{ route('rooms.vacant-forecast') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header" style="background-color: rgba(59, 130, 246, 0.1); border-bottom: 1px solid #3b82f6;">
        <h2 class="card-title" style="color: #1d4ed8; font-weight: 700;">
            <i class="bi bi-calendar2-check"></i> Perkiraan Kosong Bulan {{ \Carbon\Carbon::now()->setMonth((int)$currentMonth)->translatedFormat('F') }} {{ $currentYear }}
        </h2>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 120px;">Kamar</th>
                    <th>Nama Penyewa Saat Ini</th>
                    <th>Tanggal Masuk</th>
                    <th>Tanggal Habis Kontrak</th>
                    <th>Kontak (WA)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                    <tr>
                        <td><strong>Kamar {{ $tenant->room->room_number ?? '—' }}</strong></td>
                        <td>
                            <a href="{{ route('tenants.show', $tenant->id) }}" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">
                                {{ $tenant->name }}
                            </a>
                        </td>
                        <td style="color: #475569;">{{ \Carbon\Carbon::parse($tenant->start_date)->translatedFormat('d F Y') }}</td>
                        <td style="color: #b91c1c; font-weight: 600;">{{ \Carbon\Carbon::parse($tenant->end_date)->translatedFormat('d F Y') }}</td>
                        <td>
                            @if($tenant->phone_wa)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tenant->phone_wa) }}" target="_blank" class="btn btn-sm btn-success" style="padding: 4px 8px; font-size: 12px;">
                                    <i class="bi bi-whatsapp"></i> Hubungi
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #64748b;">Tidak ada data kamar yang diperkirakan kosong pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
