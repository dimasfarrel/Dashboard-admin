@extends('layouts.public')
@section('title', 'Beranda — Sewa Kost Mahasiswa di Malang')

@section('content')

{{-- ===== HERO ===== --}}
<section class="max-w-3xl mx-auto px-4 sm:px-6 pt-10 pb-6 text-center lg:pt-16 lg:pb-10">
    <h1 class="text-[1.75rem] sm:text-[2.125rem] font-bold leading-tight mb-3" style="color:var(--foreground);">
        Cari kost di dekat Kolose Santo Yusup <br> (HUA - IND)
    </h1>
    
    <p class="text-base" style="color:white; max-width:480px; margin:0 auto;">
        Jalan Simpang Borobudur No.1 Malang
    </p>

    <p class="text-base" style="color:var(--muted-foreground); max-width:480px; margin:0 auto;">
        Temukan kamar yang nyaman, fasilitas lengkap, dan lokasi strategis di Malang.
    </p>
</section>

{{-- ===== KAMAR TERSEDIA ===== --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12" aria-labelledby="featured-heading">
    <div class="flex justify-between items-center mb-6">
        <h2 class="section-title mb-0" id="featured-heading">Kamar Tersedia</h2>
        <a href="{{ route('public.rooms.index') }}" class="text-sm font-medium" style="color:var(--primary); text-decoration:none;">
            Lihat semua →
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($featuredRooms as $room)
        <a href="{{ route('public.rooms.show', $room) }}" class="kost-card" aria-label="Detail Kamar {{ $room->room_number }}">
            {{-- Foto --}}
            <div class="relative" style="aspect-ratio:4/3; background:var(--card);">
                @if($room->photo)
                    <img src="{{ Storage::url($room->photo) }}"
                         alt="Foto kamar {{ $room->room_number }}"
                         class="w-full h-full object-cover"
                         loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center" style="color:var(--muted-foreground);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.83 0L6 21"/></svg>
                    </div>
                @endif
                {{-- Badge tipe --}}
                <div class="absolute top-3 left-3">
                    <span class="badge badge-{{ $room->type == 'Putri' ? 'putri' : ($room->type == 'Putra' ? 'putra' : 'campur') }}">
                        {{ $room->type ?? 'Campur' }}
                    </span>
                </div>
                {{-- Status --}}
                @if($room->status !== 'available')
                    <div class="absolute top-3 right-3">
                        <span class="badge badge-danger">Penuh</span>
                    </div>
                @endif
            </div>

            {{-- Body --}}
            <div class="p-4">
                <h3 class="font-semibold text-lg leading-tight mb-1" style="color:var(--foreground); overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                    Kamar {{ $room->room_number }}
                </h3>
                <p class="text-sm mb-3" style="color:var(--muted-foreground);">
                    Lantai {{ $room->floor }}
                    @if($room->size_sqm) &bull; {{ $room->size_sqm }} m²@endif
                </p>

                {{-- Fasilitas 3 pertama --}}
                @if($room->facilities->count())
                <div class="flex flex-wrap gap-1.5 mb-3">
                    @foreach($room->facilities->take(3) as $fac)
                        <span class="text-xs px-2 py-1 rounded-full" style="background:var(--card); border:1px solid var(--border); color:var(--muted-foreground);">{{ $fac->name }}</span>
                    @endforeach
                    @if($room->facilities->count() > 3)
                        <span class="text-xs px-2 py-1 rounded-full" style="background:var(--card); border:1px solid var(--border); color:var(--muted-foreground);">+{{ $room->facilities->count() - 3 }}</span>
                    @endif
                </div>
                @endif

                <p class="font-bold text-xl" style="color:var(--foreground);">
                    Rp {{ number_format($room->price, 0, ',', '.') }}
                    <span class="text-sm font-normal" style="color:var(--muted-foreground);">/bln</span>
                </p>
            </div>
        </a>
        @empty
        <div class="col-span-full py-12 text-center" style="color:var(--muted-foreground);">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-4" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.83 0L6 21"/></svg>
            <p>Belum ada kamar yang tersedia.</p>
        </div>
        @endforelse
    </div>
</section>

{{-- ===== KENAPA KAMI ===== --}}
<section class="border-t py-16" style="border-color:var(--border);" aria-labelledby="why-heading">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="section-title text-center mb-10" id="why-heading">Kenapa Memilih Kami?</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach([
                ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Keamanan 24 Jam', 'desc' => 'CCTV dan akses terkontrol untuk kenyamanan Anda sepanjang waktu.'],
                ['icon' => 'M8.111 16.404a5.5 5.5 0 0 1 7.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0', 'title' => 'WiFi Kencang', 'desc' => 'Internet cepat di setiap kamar — cocok untuk belajar online.'],
                ['icon' => 'M12 18h.01M8 21h8a2 2 0 0 0 2-2v-1H6v1a2 2 0 0 0 2 2zM4 10h16M4 10a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6H4v-6z', 'title' => 'Booking Online', 'desc' => 'Pilih kamar dan bayar lewat HP — tanpa perlu datang langsung dulu.'],
            ] as $item)
            <div class="p-6 rounded-2xl text-center" style="background:var(--card); border:1px solid var(--border);">
                <div class="w-12 h-12 rounded-xl mx-auto mb-4 flex items-center justify-center" style="background:color-mix(in srgb, var(--primary) 10%, transparent);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="color:var(--primary);"><path d="{{ $item['icon'] }}"/></svg>
                </div>
                <h3 class="font-semibold text-base mb-2" style="color:var(--foreground);">{{ $item['title'] }}</h3>
                <p class="text-sm leading-relaxed" style="color:var(--muted-foreground);">{{ $item['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
