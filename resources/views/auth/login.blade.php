@extends('layouts.auth', ['title' => 'Login'])

@section('content')
<div class="mb-5">
    <h2 class="text-xl font-bold text-[#101828] mb-1 tracking-tight">Masuk ke Akun</h2>
    <p class="text-sm text-[#667085] m-0">Masukkan email dan password Anda untuk melanjutkan.</p>
</div>

@if (session('status'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-3.5 py-2.5 rounded-lg text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-3.5 py-2.5 rounded-lg text-xs">
        <ul class="list-none space-y-1 m-0 p-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-[#101828] mb-1.5">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-2 focus:ring-[#062072] focus:border-[#062072] transition-colors"
            placeholder="nama@email.com">
        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-1.5">
            <label for="password" class="block text-sm font-medium text-[#101828]">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[#062072] hover:text-[#04184F] hover:underline">Lupa password?</a>
        </div>
        <input type="password" id="password" name="password" required
            class="w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-2 focus:ring-[#062072] focus:border-[#062072] transition-colors"
            placeholder="••••••••">
        @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-2">
        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-[#D0D5DD] text-[#062072] focus:ring-[#062072] cursor-pointer">
        <label for="remember" class="text-sm text-[#344054] cursor-pointer select-none">Ingat saya</label>
    </div>

    <button type="submit" id="btn-login"
        class="w-full h-11 px-4 bg-[#062072] hover:bg-[#04184F] active:scale-[0.98] text-white font-semibold text-sm rounded-lg transition-all flex items-center justify-center shadow-xs cursor-pointer">
        Masuk
    </button>
</form>

<p class="mt-5 text-center text-sm text-[#667085] m-0">
    Belum punya akun?
    <a href="{{ route('register') }}" class="text-[#062072] font-semibold hover:underline">Daftar Sekarang</a>
</p>
@endsection
