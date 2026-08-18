@extends('layouts.app')
@section('title', 'Riwayat Aktivitas (Log)')

@section('page-title', 'Riwayat Aktivitas (Log)')
@section('page-subtitle', 'Melacak semua perubahan data (Tambah, Edit, Hapus) di dalam sistem')

@section('topbar-actions')
    <a href="{{ route('system-logs.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
    </a>
@endsection

@section('content')

<div class="card filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('system-logs.index') }}" class="flex items-center gap-3">
            <div style="flex: 1;">
                <label for="menu" style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 5px; display: block;">Filter berdasarkan Menu:</label>
                <select name="menu" id="menu" class="form-control" onchange="this.form.submit()" style="max-width: 300px;">
                    <option value="">-- Semua Menu --</option>
                    @foreach($menus as $menuOption)
                        <option value="{{ $menuOption }}" {{ $selectedMenu == $menuOption ? 'selected' : '' }}>
                            {{ $menuOption }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><i class="bi bi-clock-history"></i> Daftar Log Sistem</h2>
    </div>
    
    <div class="table-responsive">
        <table class="table" style="min-width: 800px;">
            <thead>
                <tr>
                    <th style="width: 250px;">Waktu & Tanggal</th>
                    <th style="width: 250px;">Event (Nama Menu)</th>
                    <th>Detail Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="color: var(--text-secondary); font-family: monospace; font-size: 13px;">
                            [{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s, d-m-Y') }}]
                        </td>
                        <td>
                            @php
                                $badgeClass = 'bg-secondary';
                                if ($log->action === 'Ditambahkan') $badgeClass = 'bg-success';
                                elseif ($log->action === 'Diperbarui') $badgeClass = 'bg-warning text-dark';
                                elseif ($log->action === 'Dihapus') $badgeClass = 'bg-danger';
                            @endphp
                            <span class="badge {{ $badgeClass }}" style="padding: 5px 8px; font-size: 11px;">
                                {{ $log->action }}
                            </span>
                            <strong style="margin-left: 8px; color: var(--text-primary);">({{ $log->menu }})</strong>
                        </td>
                        <td style="color: var(--text-secondary);">
                            <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">{{ $log->description }}</div>
                            @if($log->action === 'Diperbarui' && is_array($log->old_data) && is_array($log->new_data))
                                @php
                                    $changes = [];
                                    foreach ($log->new_data as $key => $newValue) {
                                        if (in_array($key, ['updated_at', 'created_at'])) continue;
                                        $oldValue = $log->old_data[$key] ?? null;
                                        if ($oldValue != $newValue) {
                                            $changes[$key] = [
                                                'old' => $oldValue,
                                                'new' => $newValue
                                            ];
                                        }
                                    }
                                @endphp
                                @if(count($changes) > 0)
                                    <div style="margin-top: 8px; font-size: 12px; background: var(--surface-2); padding: 10px; border-radius: 6px; border: 1px solid var(--border-color);">
                                        <div style="font-weight: 600; margin-bottom: 6px; color: var(--text-primary);"><i class="bi bi-info-circle"></i> Detail Perubahan:</div>
                                        <ul style="margin: 0; padding-left: 18px; color: var(--text-primary);">
                                            @foreach($changes as $key => $change)
                                                <li style="margin-bottom: 3px;">
                                                    <strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> 
                                                    <span style="color: #dc3545; text-decoration: line-through;">{{ $change['old'] ?: '(kosong)' }}</span> 
                                                    <i class="bi bi-arrow-right" style="margin: 0 4px; color: #6c757d; font-size: 10px;"></i> 
                                                    <span style="color: #198754; font-weight: 600;">{{ $change['new'] ?: '(kosong)' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px 20px; color: var(--text-secondary);">
                            <div style="font-size: 40px; margin-bottom: 12px;"><i class="bi bi-journal-x"></i></div>
                            <div>Belum ada aktivitas yang dicatat.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($logs->hasPages())
    <div class="card-body" style="border-top: 1px solid var(--border-color); padding: 15px 24px;">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<style>
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    .bg-success { background-color: #198754 !important; }
    .bg-warning { background-color: #ffc107 !important; color: #000; }
    .bg-danger { background-color: #dc3545 !important; }
    .bg-secondary { background-color: #6c757d !important; }
</style>

@endsection
