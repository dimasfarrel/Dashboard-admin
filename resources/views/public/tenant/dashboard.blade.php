@extends('layouts.public')
@section('title', 'Dashboard Penyewa — KostMalang')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[1.75rem] font-bold mb-1" style="color:var(--foreground);">Dashboard Saya</h1>
        <p class="text-sm" style="color:var(--muted-foreground);">
            Halo, <strong style="color:var(--foreground);">{{ $user->name }}</strong>. Berikut riwayat pemesanan kamar Anda.
        </p>
    </div>

    {{-- Kartu Identitas --}}
    <div class="rounded-2xl p-6 mb-8 flex items-center gap-5" style="background:var(--card); border:1px solid var(--border);">
        <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold shrink-0" 
             style="background:color-mix(in srgb, var(--primary) 15%, transparent); color:var(--primary);">
            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <p class="font-semibold text-xl leading-tight" style="color:var(--foreground);">{{ $user->name }}</p>
            <p class="text-sm mt-1" style="color:var(--muted-foreground);">{{ $user->email }}</p>
        </div>
        <a href="{{ route('public.rooms.index') }}" class="btn-primary shrink-0" style="height:40px; font-size:0.875rem; padding:0 1.25rem;">
            + Sewa Kamar
        </a>
    </div>

    {{-- Riwayat Pemesanan --}}
    <section aria-labelledby="booking-heading">
        <h2 class="section-title mb-4" id="booking-heading">Riwayat Pemesanan</h2>

        @if($bookings->count())
        <div class="space-y-4">
            @foreach($bookings as $booking)
            <div class="rounded-2xl p-5" style="background:var(--card); border:1px solid var(--border);">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="font-semibold text-base" style="color:var(--foreground);">
                                Kamar {{ $booking->room->room_number }}
                            </span>
                            @if($booking->status == 'pending')
                                <span class="badge badge-waiting">Menunggu Verifikasi</span>
                            @elseif($booking->status == 'approved')
                                <span class="badge badge-success">Disetujui & Aktif</span>
                            @elseif($booking->status == 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                            @else
                                <span class="badge" style="background:var(--border); color:var(--muted-foreground);">{{ ucfirst($booking->status) }}</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm" style="color:var(--muted-foreground);">
                            <span>
                                <strong style="color:var(--foreground);">Masuk:</strong>
                                {{ \Carbon\Carbon::parse($booking->start_date)->translatedFormat('d M Y') }}
                            </span>
                            <span>
                                <strong style="color:var(--foreground);">Durasi:</strong>
                                {{ $booking->duration_months }} Bulan
                            </span>
                            <span>
                                <strong style="color:var(--foreground);">ID:</strong>
                                #{{ $booking->id }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-bold text-xl" style="color:var(--foreground);">
                            Rp {{ number_format($booking->total_amount, 0, ',', '.') }}
                        </p>
                        <p class="text-xs mt-0.5" style="color:var(--muted-foreground);">Total pembayaran</p>
                    </div>
                </div>

                @if($booking->status == 'pending')
                <div class="mt-4 pt-4 flex items-center gap-2 text-sm" style="border-top:1px solid var(--border); color:var(--muted-foreground);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Admin sedang memverifikasi bukti pembayaran Anda. Biasanya dalam 1×24 jam.
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="py-16 text-center rounded-2xl" style="background:var(--card); border:1px solid var(--border);">
            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-4" aria-hidden="true" style="color:var(--muted-foreground);"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            <h3 class="font-semibold text-lg mb-2" style="color:var(--foreground);">Belum ada pemesanan</h3>
            <p class="text-sm mb-6" style="color:var(--muted-foreground);">Yuk, cari kamar kost yang cocok untuk Anda!</p>
            <a href="{{ route('public.rooms.index') }}" class="btn-primary" style="height:44px; font-size:0.9375rem; padding:0 1.5rem;">
                Cari Kamar Sekarang
            </a>
        </div>
        @endif
    </section>
</div>
@endsection
