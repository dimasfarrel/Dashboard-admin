@extends('layouts.app')
@section('title', 'Manajemen Booking')
@section('page-title', 'Booking Online')
@section('page-subtitle', 'Daftar pemesanan kamar dari website publik')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">Daftar Booking Masuk</div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pemesan</th>
                    <th>Kamar</th>
                    <th>Tgl Mulai</th>
                    <th>Durasi</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr>
                    <td>#{{ $booking->id }}</td>
                    <td>
                        <strong>{{ $booking->user->name }}</strong><br>
                        <span class="text-muted text-sm">{{ $booking->phone_wa }}</span>
                    </td>
                    <td>Kamar {{ $booking->room->room_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ $booking->duration_months }} Bulan</td>
                    <td class="money-text">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</td>
                    <td>
                        @if($booking->status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($booking->status == 'approved')
                            <span class="badge badge-success">Disetujui</span>
                        @elseif($booking->status == 'rejected')
                            <span class="badge badge-danger">Ditolak</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($booking->status) }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-info">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">Belum ada data booking.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
