@extends('layouts.app')
@section('title', 'Detail Booking')
@section('page-title', 'Detail Booking #'.$booking->id)
@section('page-subtitle', 'Informasi lengkap pemesanan dan bukti pembayaran')

@section('topbar-actions')
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
@endsection

@section('content')
<div style="display:grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    {{-- Info Pemesan & Kamar --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Informasi Pemesan</div>
        </div>
        <div class="card-body">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); width: 150px; color: var(--text-muted);">Nama</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);"><strong>{{ $booking->user->name }}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">Email</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">{{ $booking->user->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">NIK</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">{{ $booking->nik }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">No WhatsApp</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->phone_wa) }}" target="_blank" class="text-success" style="text-decoration: none;">
                            <i class="bi bi-whatsapp"></i> {{ $booking->phone_wa }}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">Jenis Kelamin</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">{{ ucfirst($booking->gender) }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">Kamar Tujuan</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">
                        <a href="{{ route('rooms.show', $booking->room) }}">Kamar {{ $booking->room->room_number }}</a>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">Tanggal Masuk</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">{{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">Durasi Sewa</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color);">{{ $booking->duration_months }} Bulan</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">Total Tagihan</td>
                    <td style="padding: 10px; border-bottom: 1px solid var(--border-color); font-size: 18px; font-weight: bold; color: var(--accent-primary);">
                        Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Aksi & Bukti Transfer --}}
    <div style="display:flex; flex-direction:column; gap: 20px;">
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">Status: 
                    @if($booking->status == 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($booking->status == 'approved')
                        <span class="badge badge-success">Disetujui</span>
                    @elseif($booking->status == 'rejected')
                        <span class="badge badge-danger">Ditolak</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($booking->status) }}</span>
                    @endif
                </div>
            </div>
            
            @if($booking->status == 'pending')
            <div class="card-body" style="display:flex; flex-direction:column; gap: 10px;">
                <form action="{{ route('admin.bookings.approve', $booking) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" style="width: 100%;" onclick="return confirm('Apakah Anda yakin ingin MENYETUJUI booking ini dan membuat data sewa aktif?')">
                        <i class="bi bi-check-circle"></i> Setujui & Buat Sewa
                    </button>
                </form>

                <form action="{{ route('admin.bookings.reject', $booking) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="width: 100%;" onclick="return confirm('Tolak booking ini?')">
                        <i class="bi bi-x-circle"></i> Tolak Booking
                    </button>
                </form>
            </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Bukti Transfer</div>
            </div>
            <div class="card-body text-center">
                @if($booking->payment_proof)
                    <a href="{{ Storage::url($booking->payment_proof) }}" target="_blank">
                        <img src="{{ Storage::url($booking->payment_proof) }}" alt="Bukti Transfer" style="max-width: 100%; border-radius: 8px; border: 1px solid var(--border-color);">
                    </a>
                    <div style="margin-top: 10px;">
                        <a href="{{ Storage::url($booking->payment_proof) }}" target="_blank" class="btn btn-sm btn-secondary">
                            <i class="bi bi-zoom-in"></i> Lihat Penuh
                        </a>
                    </div>
                @else
                    <div style="padding: 30px; background: var(--surface-2); border-radius: 8px; color: var(--text-muted);">
                        <i class="bi bi-image" style="font-size: 2rem;"></i><br>
                        Penyewa belum/tidak mengunggah bukti transfer.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
