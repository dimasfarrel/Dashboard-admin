@extends('layouts.public')
@section('title', 'Pesan Kamar ' . $room->room_number . ' — KostMalang')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

    <div class="mb-8 text-center">
        <h1 class="text-[1.75rem] font-bold mb-1" style="color:var(--foreground);">Formulir Penyewaan</h1>
        <p class="text-sm" style="color:var(--muted-foreground);">
            Lengkapi data diri dan unggah bukti transfer untuk memesan <strong>Kamar {{ $room->room_number }}</strong>.
        </p>
    </div>

    <form action="{{ route('public.booking.store', $room) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Ringkasan Kamar --}}
        <div class="rounded-2xl p-5 flex justify-between items-center gap-4" 
             style="background:color-mix(in srgb, var(--primary) 8%, var(--background)); border:1px solid color-mix(in srgb, var(--primary) 25%, transparent);">
            <div>
                <p class="text-sm font-medium" style="color:var(--primary);">Kamar yang dipesan</p>
                <p class="font-bold text-xl mt-0.5" style="color:var(--foreground);">Kamar {{ $room->room_number }}</p>
                <p class="text-sm mt-0.5" style="color:var(--muted-foreground);">Lantai {{ $room->floor }} &bull; {{ $room->type ?? 'Standard' }}</p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-medium" style="color:var(--primary);">Harga per bulan</p>
                <p class="font-bold text-2xl mt-0.5" style="color:var(--foreground);">Rp {{ number_format($room->price, 0, ',', '.') }}</p>
                <input type="hidden" id="room-price" value="{{ $room->price }}">
            </div>
        </div>

        {{-- Data Diri & Waktu Sewa --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Data Diri --}}
            <div class="rounded-2xl p-6 space-y-5" style="background:var(--card); border:1px solid var(--border);">
                <h2 class="font-semibold" style="color:var(--foreground);">
                    Data Diri Penyewa
                </h2>

                <div>
                    <label for="nik" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Nomor Identitas (KTP/SIM/dll) <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required
                           class="input-field @error('nik') is-invalid @enderror" placeholder="Nomor identitas Anda">
                    @error('nik') <span class="text-xs mt-1 block" style="color:#dc2626;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="phone_wa" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">No. WhatsApp <span style="color:#dc2626;">*</span></label>
                    <input type="tel" id="phone_wa" name="phone_wa" value="{{ old('phone_wa') }}" required
                           class="input-field @error('phone_wa') is-invalid @enderror" placeholder="Contoh: 08123456789">
                    @error('phone_wa') <span class="text-xs mt-1 block" style="color:#dc2626;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Jenis Kelamin <span style="color:#dc2626;">*</span></label>
                    <select id="gender" name="gender" required class="input-field">
                        <option value="">-- Pilih --</option>
                        <option value="laki-laki" {{ old('gender') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="perempuan" {{ old('gender') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender') <span class="text-xs mt-1 block" style="color:#dc2626;">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Waktu Sewa --}}
            <div class="rounded-2xl p-6 space-y-5" style="background:var(--card); border:1px solid var(--border);">
                <h2 class="font-semibold" style="color:var(--foreground);">Waktu Sewa</h2>

                <div>
                    <label for="start_date" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Tanggal Mulai <span style="color:#dc2626;">*</span></label>
                    <input type="date" id="start_date" name="start_date" 
                           value="{{ old('start_date', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}"
                           class="input-field">
                    @error('start_date') <span class="text-xs mt-1 block" style="color:#dc2626;">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Durasi Sewa <span style="color:#dc2626;">*</span></label>
                    <select id="duration" name="duration_months" required class="input-field" onchange="calcTotal()">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('duration_months') == $i ? 'selected' : '' }}>{{ $i }} Bulan</option>
                        @endfor
                    </select>
                </div>

                {{-- Total --}}
                <div class="rounded-xl p-4" style="background:var(--background); border:1px solid var(--border);">
                    <div class="flex justify-between text-sm mb-2" style="color:var(--muted-foreground);">
                        <span>Harga sewa (<span id="dur-lbl">1 bulan</span>)</span>
                        <span id="sub-total">Rp {{ number_format($room->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-bold" style="padding-top:0.5rem; border-top:1px solid var(--border);">
                        <span style="color:var(--foreground);">Total Tagihan</span>
                        <span style="color:var(--primary);" id="grand-total">Rp {{ number_format($room->price, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bukti Transfer --}}
        <div class="rounded-2xl p-6" style="background:var(--card); border:1px solid var(--border);">
            <h2 class="font-semibold mb-1" style="color:var(--foreground);">Bukti Transfer</h2>
            <p class="text-sm mb-4" style="color:var(--muted-foreground);">
                Transfer sesuai <strong>Total Tagihan</strong> ke:<br>
                <span class="font-medium" style="color:var(--foreground);">BCA: 1234567890 a.n. Pemilik Kost Malang</span>
            </p>

            <div id="upload-area" class="flex flex-col items-center justify-center p-8 rounded-xl cursor-pointer transition-colors"
                 style="border:2px dashed var(--border);"
                 onclick="document.getElementById('payment_proof').click()"
                 onmouseenter="this.style.borderColor='var(--primary)'"
                 onmouseleave="this.style.borderColor='var(--border)'">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mb-3" aria-hidden="true" style="color:var(--muted-foreground);"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                <p class="font-medium text-sm" style="color:var(--foreground);">Klik untuk pilih gambar</p>
                <p class="text-xs mt-1" style="color:var(--muted-foreground);">JPG, PNG — Maks. 2MB</p>
            </div>

            <input type="file" id="payment_proof" name="payment_proof" accept="image/*" required class="sr-only" onchange="showFile(this)">

            <div id="file-info" class="hidden mt-3 flex items-center gap-2 text-sm" style="color:var(--primary);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span id="file-name-text"></span>
            </div>
            @error('payment_proof') <p class="text-xs mt-2" style="color:#dc2626;">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 justify-end">
            <a href="{{ route('public.rooms.show', $room) }}" class="btn-ghost" style="height:48px;">Batal</a>
            <button type="submit" class="btn-cta" style="height:52px; font-size:1rem; padding:0 2rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Kirim Pesanan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const price = parseInt(document.getElementById('room-price').value);
const fmt = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

function calcTotal() {
    const dur = parseInt(document.getElementById('duration').value);
    const total = price * dur;
    document.getElementById('dur-lbl').textContent = dur + ' bulan';
    document.getElementById('sub-total').textContent = fmt.format(total);
    document.getElementById('grand-total').textContent = fmt.format(total);
}

function showFile(input) {
    const info = document.getElementById('file-info');
    if (input.files && input.files[0]) {
        document.getElementById('file-name-text').textContent = input.files[0].name;
        info.classList.remove('hidden');
    }
}
</script>
@endpush
