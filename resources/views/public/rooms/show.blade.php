@extends('layouts.public')
@section('title', 'Kamar ' . $room->room_number . ' — KostMalang')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10">

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-6">
        <ol class="flex items-center gap-2 text-sm" style="color:var(--muted-foreground);">
            <li><a href="{{ route('public.home') }}" style="color:var(--muted-foreground); text-decoration:none;">Beranda</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('public.rooms.index') }}" style="color:var(--muted-foreground); text-decoration:none;">Kamar</a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" style="color:var(--foreground); font-weight:500;">Kamar {{ $room->room_number }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ===== KIRI: Foto + Info ===== --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Image Gallery --}}
            <div class="rounded-2xl overflow-hidden" style="background:var(--card); border:1px solid var(--border);">
                {{-- Gambar utama --}}
                <div class="relative" style="aspect-ratio:16/9; background:var(--card);">
                    @if($room->photo)
                        <img id="main-img" src="{{ Storage::url($room->photo) }}"
                             alt="Foto kamar {{ $room->room_number }}"
                             class="w-full h-full object-cover transition-opacity duration-200">
                    @else
                        <div class="w-full h-full flex items-center justify-center" style="color:var(--muted-foreground);" id="main-img-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.83 0L6 21"/></svg>
                        </div>
                    @endif
                </div>

                {{-- Strip thumbnail --}}
                @php $allImages = []; 
                    if($room->photo) $allImages[] = Storage::url($room->photo);
                    foreach($room->images as $img) $allImages[] = Storage::url($img->image_path);
                @endphp
                @if(count($allImages) > 1)
                <div class="flex gap-2 p-2 overflow-x-auto snap-x" style="border-top:1px solid var(--border);">
                    @foreach($allImages as $i => $src)
                    <button type="button"
                            onclick="document.getElementById('main-img').src='{{ $src }}'; setActiveThumb(this);"
                            class="thumb-btn flex-shrink-0 rounded-lg overflow-hidden snap-start transition-all"
                            style="width:80px; height:60px; border:2px solid {{ $i === 0 ? 'var(--primary)' : 'transparent' }}; cursor:pointer; padding:0;"
                            aria-label="Foto {{ $i + 1 }}">
                        <img src="{{ $src }}" alt="" class="w-full h-full object-cover" loading="lazy" aria-hidden="true">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Info Utama --}}
            <div class="rounded-2xl p-6" style="background:var(--card); border:1px solid var(--border);">
                <div class="flex flex-wrap items-start gap-3 mb-4">
                    <div class="flex-1">
                        <h1 class="text-[1.75rem] font-bold leading-tight" style="color:var(--foreground);">
                            Kamar {{ $room->room_number }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-sm" style="color:var(--muted-foreground);">
                            <span>Lantai {{ $room->floor }}</span>
                            @if($room->size_sqm)<span>&bull; {{ $room->size_sqm }} m²</span>@endif
                            @if($room->type)
                                <span class="badge badge-{{ strtolower($room->type) == 'putri' ? 'putri' : (strtolower($room->type) == 'putra' ? 'putra' : 'campur') }}">{{ $room->type }}</span>
                            @endif
                            @if($room->status == 'available')
                                <span class="badge badge-success">Tersedia</span>
                            @elseif($room->status == 'occupied')
                                <span class="badge badge-danger">Penuh</span>
                            @else
                                <span class="badge badge-waiting">Maintenance</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Harga --}}
                <div class="py-4 mb-4" style="border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
                    <p class="text-[1.875rem] font-bold leading-none" style="color:var(--foreground);">
                        Rp {{ number_format($room->price, 0, ',', '.') }}<span class="text-base font-normal" style="color:var(--muted-foreground);">/bulan</span>
                    </p>
                </div>

                {{-- Deskripsi --}}
                @if($room->description)
                <div>
                    <h2 class="font-semibold mb-2" style="color:var(--foreground);">Deskripsi</h2>
                    <p class="text-sm leading-relaxed" style="color:var(--muted-foreground); line-height:1.65;">
                        {{ $room->description }}
                    </p>
                </div>
                @endif
            </div>

            {{-- Fasilitas --}}
            @if($room->facilities->count())
            <div class="rounded-2xl p-6" style="background:var(--card); border:1px solid var(--border);" aria-labelledby="fac-heading">
                <h2 class="font-semibold mb-4" id="fac-heading" style="color:var(--foreground);">Fasilitas Tersedia</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($room->facilities as $fac)
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" 
                             style="background:color-mix(in srgb, var(--primary) 10%, transparent);">
                            <i class="{{ $fac->icon }}" style="color:var(--primary);"></i>
                        </div>
                        <span class="text-sm font-medium" style="color:var(--foreground);">{{ $fac->name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ===== KANAN: Sticky Booking Card ===== --}}
        <div class="lg:col-span-1">
            <div class="rounded-2xl p-6 sticky top-24" style="background:var(--card); border:1px solid var(--border); box-shadow:0 4px 16px rgba(0,0,0,0.08);">
                <div class="mb-6">
                    <p class="text-sm mb-1" style="color:var(--muted-foreground);">Harga Sewa</p>
                    <p class="text-[1.875rem] font-bold" style="color:var(--foreground);">
                        Rp {{ number_format($room->price, 0, ',', '.') }}
                        <span class="text-base font-normal" style="color:var(--muted-foreground);">/bulan</span>
                    </p>
                </div>

                <div class="space-y-3 mb-6 text-sm">
                    <div class="flex justify-between py-2" style="border-bottom:1px solid var(--border);">
                        <span style="color:var(--muted-foreground);">Status</span>
                        @if($room->status == 'available')
                            <span class="badge badge-success">Tersedia</span>
                        @elseif($room->status == 'occupied')
                            <span class="badge badge-danger">Penuh</span>
                        @else
                            <span class="badge badge-waiting">Maintenance</span>
                        @endif
                    </div>
                    <div class="flex justify-between py-2" style="border-bottom:1px solid var(--border);">
                        <span style="color:var(--muted-foreground);">Lantai</span>
                        <span style="color:var(--foreground); font-weight:500;">{{ $room->floor }}</span>
                    </div>
                    @if($room->size_sqm)
                    <div class="flex justify-between py-2" style="border-bottom:1px solid var(--border);">
                        <span style="color:var(--muted-foreground);">Ukuran</span>
                        <span style="color:var(--foreground); font-weight:500;">{{ $room->size_sqm }} m²</span>
                    </div>
                    @endif
                </div>

                @if($room->status == 'available')
                    <a href="{{ route('public.booking.checkout', $room) }}" class="btn-cta w-full" style="width:100%;"
                       aria-label="Pesan Kamar {{ $room->room_number }} sekarang">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                        Pesan / Sewa Sekarang
                    </a>
                    <p class="text-center text-xs mt-3" style="color:var(--muted-foreground);">Proses aman & cepat. Booking bisa dibatalkan.</p>
                @else
                    <button disabled class="w-full h-14 rounded-[10px] font-semibold text-sm cursor-not-allowed"
                            style="background:var(--border); color:var(--muted-foreground);">
                        Kamar Tidak Tersedia
                    </button>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Mobile Sticky Bottom Bar --}}
<div class="lg:hidden fixed bottom-16 left-0 right-0 z-30 px-4 pb-3 pt-2" 
     style="background:var(--background); border-top:1px solid var(--border);">
    @if($room->status == 'available')
        <a href="{{ route('public.booking.checkout', $room) }}" class="btn-cta" style="width:100%;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            Pesan Sekarang — Rp {{ number_format($room->price, 0, ',', '.') }}/bln
        </a>
    @else
        <button disabled class="w-full h-14 rounded-[10px] font-semibold text-sm cursor-not-allowed"
                style="background:var(--border); color:var(--muted-foreground);">
            Kamar Tidak Tersedia
        </button>
    @endif
</div>

@push('scripts')
<script>
function setActiveThumb(btn) {
    document.querySelectorAll('.thumb-btn').forEach(b => b.style.borderColor = 'transparent');
    btn.style.borderColor = 'var(--primary)';
}
</script>
@endpush

@endsection
