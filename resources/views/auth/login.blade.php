@extends('layouts.public')
@section('title', 'Masuk — KostMalang')

@section('content')
<div class="min-h-[calc(100vh-8rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-[400px]">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-xl mx-auto mb-4 flex items-center justify-center text-white font-bold text-lg" style="background:var(--primary);">K</div>
            <h1 class="text-[1.75rem] font-bold" style="color:var(--foreground);">Masuk</h1>
            <p class="mt-1 text-sm" style="color:var(--muted-foreground);">Selamat datang kembali di KostMalang</p>
        </div>

        <div class="rounded-2xl p-6 sm:p-8" style="background:var(--card); border:1px solid var(--border);">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           class="input-field @error('email') is-invalid @enderror"
                           placeholder="nama@email.com" aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}">
                    @error('email')
                        <div id="email-error" class="flex items-center gap-1.5 mt-1.5" role="alert" style="color:#dc2626;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                            <span class="text-xs">{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="text-sm font-medium" style="color:var(--foreground);">Password</label>
                    </div>
                    <div class="relative">
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               class="input-field pr-12 @error('password') is-invalid @enderror"
                               placeholder="Masukkan password">
                        <button type="button" id="toggle-password"
                                aria-label="Tampilkan atau sembunyikan password"
                                onclick="togglePwd()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center w-8 h-8 rounded-md transition-colors"
                                style="color:var(--muted-foreground); background:transparent; border:none; cursor:pointer;">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2.5 cursor-pointer" style="color:var(--muted-foreground); font-size:0.875rem;">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded" style="accent-color:var(--primary);">
                    Ingat saya
                </label>

                <button type="submit" class="btn-primary" style="width:100%; height:52px; font-size:1rem;">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-sm mt-6" style="color:var(--muted-foreground);">
            Belum punya akun?
            <a href="{{ route('register') }}" style="color:var(--primary); font-weight:600; text-decoration:none;">Daftar</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePwd() {
    const input = document.getElementById('password');
    const eyeOn = document.getElementById('eye-icon');
    const eyeOff = document.getElementById('eye-off-icon');
    if (input.type === 'password') {
        input.type = 'text';
        eyeOn.style.display = 'none';
        eyeOff.style.display = '';
    } else {
        input.type = 'password';
        eyeOn.style.display = '';
        eyeOff.style.display = 'none';
    }
}
</script>
@endpush
