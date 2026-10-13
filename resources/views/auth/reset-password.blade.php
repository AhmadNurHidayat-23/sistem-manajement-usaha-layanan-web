@extends('layouts.auth', ['title' => 'Reset Password'])

@section('content')
<div class="flex items-center gap-3 mb-5">
    <div class="w-10 h-10 rounded-xl bg-[#DCE8FF] flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
    </div>
    <div>
        <h2 class="text-xl font-bold text-[#101828] m-0">Reset Password</h2>
        <p class="text-xs text-[#667085] m-0">Masukkan password baru untuk akun Anda.</p>
    </div>
</div>

@if ($errors->any())
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm">
        @foreach ($errors->all() as $error)
            <p class="m-0">{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('password.store') }}" class="space-y-4">
    @csrf

    {{-- Hidden token & email passed from link --}}
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div>
        <label for="email" class="block text-sm font-medium text-[#101828] mb-1">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
            class="w-full px-3 py-2.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]"
            placeholder="nama@email.com">
        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-[#101828] mb-1">Password Baru</label>
        <input type="password" id="password" name="password" required
            class="w-full px-3 py-2.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]"
            placeholder="Minimal 8 karakter">
        @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-[#101828] mb-1">Konfirmasi Password Baru</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required
            class="w-full px-3 py-2.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] focus:outline-none focus:ring-2 focus:ring-[#062072]"
            placeholder="Ulangi password baru">
    </div>

    <button type="submit" id="btn-reset-password"
        class="w-full py-2.5 px-4 bg-[#062072] hover:bg-[#04184F] text-white font-semibold text-sm rounded-lg transition-colors flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Simpan Password Baru
    </button>
</form>
@endsection
