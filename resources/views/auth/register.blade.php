@extends('layouts.public')
@section('title', 'Daftar Akun — KostMalang')

@section('content')
<div class="min-h-[calc(100vh-8rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-[400px]">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-xl mx-auto mb-4 flex items-center justify-center text-white font-bold text-lg" style="background:var(--primary);">K</div>
            <h1 class="text-[1.75rem] font-bold" style="color:var(--foreground);">Daftar Akun</h1>
            <p class="mt-1 text-sm" style="color:var(--muted-foreground);">Buat akun untuk mulai menyewa kamar</p>
        </div>

        <div class="rounded-2xl p-6 sm:p-8" style="background:var(--card); border:1px solid var(--border);">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Nama --}}
                <div>
                    <label for="name" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name"
                           class="input-field @error('name') is-invalid @enderror"
                           placeholder="Sesuai KTP" aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}">
                    @error('name')
                        <div id="name-error" class="flex items-center gap-1.5 mt-1.5" role="alert" style="color:#dc2626;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                            <span class="text-xs">{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           class="input-field @error('email') is-invalid @enderror"
                           placeholder="nama@email.com" aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}">
                    @error('email')
                        <div id="email-error" class="flex items-center gap-1.5 mt-1.5" role="alert" style="color:#dc2626;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                            <span class="text-xs">{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required minlength="8"
                               class="input-field pr-12 @error('password') is-invalid @enderror"
                               placeholder="Minimal 8 karakter">
                        <button type="button" onclick="togglePwd('password','eye-1','eyeoff-1')" aria-label="Tampilkan password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center w-8 h-8"
                                style="color:var(--muted-foreground); background:transparent; border:none; cursor:pointer;">
                            <svg id="eye-1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeoff-1" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <div class="flex items-center gap-1.5 mt-1.5" role="alert" style="color:#dc2626;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                            <span class="text-xs">{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium mb-1.5" style="color:var(--foreground);">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                               class="input-field pr-12"
                               placeholder="Ulangi password">
                        <button type="button" onclick="togglePwd('password_confirmation','eye-2','eyeoff-2')" aria-label="Tampilkan konfirmasi password"
                                class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center w-8 h-8"
                                style="color:var(--muted-foreground); background:transparent; border:none; cursor:pointer;">
                            <svg id="eye-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeoff-2" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; height:52px; font-size:1rem;">
                    Daftar Sekarang
                </button>
            </form>
        </div>

        <p class="text-center text-sm mt-6" style="color:var(--muted-foreground);">
            Sudah punya akun?
            <a href="{{ route('login') }}" style="color:var(--primary); font-weight:600; text-decoration:none;">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePwd(inputId, eyeId, eyeOffId) {
    const input = document.getElementById(inputId);
    const eyeOn = document.getElementById(eyeId);
    const eyeOff = document.getElementById(eyeOffId);
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
