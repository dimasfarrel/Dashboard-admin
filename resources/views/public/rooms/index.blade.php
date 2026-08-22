@extends('layouts.public')
@section('title', 'Cari Kamar — KostMalang')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-[1.75rem] font-bold mb-1" style="color:var(--foreground);">Daftar Kamar</h1>
        <p class="text-sm" style="color:var(--muted-foreground);">Temukan kamar yang sesuai dengan kebutuhan dan budget Anda.</p>
    </div>

    {{-- Search + Filter bar --}}


    {{-- Ringkasan hasil --}}
    <p class="text-sm mb-6" style="color:var(--muted-foreground);">
        Menampilkan <strong style="color:var(--foreground);">{{ $rooms->total() }}</strong> kamar ditemukan
    </p>

    {{-- Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($rooms as $room)
        <a href="{{ route('public.rooms.show', $room) }}" class="kost-card" aria-label="Detail Kamar {{ $room->room_number }}">
            <div class="relative" style="aspect-ratio:4/3; background:var(--card);">
                @if($room->photo)
                    <img src="{{ Storage::url($room->photo) }}" 
                         alt="Foto kamar {{ $room->room_number }}"
                         class="w-full h-full object-cover" loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center" style="color:var(--muted-foreground);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.83 0L6 21"/></svg>
                    </div>
                @endif
                <div class="absolute top-3 left-3 flex gap-2">
                    @if($room->type)
                        <span class="badge badge-{{ strtolower($room->type) == 'putri' ? 'putri' : (strtolower($room->type) == 'putra' ? 'putra' : 'campur') }}">
                            {{ $room->type }}
                        </span>
                    @endif
                    @if($room->status == 'occupied')
                        <span class="badge badge-danger">Penuh</span>
                    @endif
                </div>
            </div>

            <div class="p-4">
                <h2 class="font-semibold text-lg mb-1 leading-tight" style="color:var(--foreground);">
                    Kamar {{ $room->room_number }}
                </h2>
                <p class="text-sm mb-3" style="color:var(--muted-foreground);">
                    Lantai {{ $room->floor }}@if($room->size_sqm) &bull; {{ $room->size_sqm }} m²@endif
                </p>

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
        <div class="col-span-full py-20 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mx-auto mb-4" aria-hidden="true" style="color:var(--muted-foreground);"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><line x1="11" x2="11" y1="8" y2="14"/><line x1="8" x2="14" y1="11" y2="11"/></svg>
            <h3 class="font-semibold text-lg mb-2" style="color:var(--foreground);">Belum ada kamar yang cocok</h3>
            <p class="text-sm mb-6" style="color:var(--muted-foreground);">Coba ubah filter pencarian Anda.</p>
            <a href="{{ route('public.rooms.index') }}" class="btn-outline" style="height:40px; font-size:0.875rem; padding:0 1.25rem;">Reset Filter</a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-10">
        {{ $rooms->withQueryString()->links() }}
    </div>
</div>
@endsection
