@extends('layouts.auth', ['title' => 'Daftar Akun'])

@section('content')
<div class="mb-5">
    <h2 class="text-xl font-bold text-[#101828] mb-1 tracking-tight">Daftar Akun Customer</h2>
    <p class="text-sm text-[#667085] m-0">Buat akun baru untuk mulai booking layanan.</p>
</div>

@if ($errors->any())
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-3.5 py-2.5 rounded-lg text-xs">
        <ul class="list-none space-y-1 m-0 p-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-4">
    @csrf

    <div>
        <label for="name" class="block text-sm font-medium text-[#101828] mb-1.5">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
            class="w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-2 focus:ring-[#062072] focus:border-[#062072] transition-colors"
            placeholder="Nama Anda">
        @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-[#101828] mb-1.5">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required
            class="w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-2 focus:ring-[#062072] focus:border-[#062072] transition-colors"
            placeholder="nama@email.com">
        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-[#101828] mb-1.5">Nomor WhatsApp / Telepon</label>
        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
            class="w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-2 focus:ring-[#062072] focus:border-[#062072] transition-colors"
            placeholder="08xxxxxxxxxx">
        @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-[#101828] mb-1.5">Password</label>
        <input type="password" id="password" name="password" required
            class="w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-2 focus:ring-[#062072] focus:border-[#062072] transition-colors"
            placeholder="Minimal 8 karakter">
        @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-[#101828] mb-1.5">Konfirmasi Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required
            class="w-full h-11 px-3.5 bg-white border border-[#D0D5DD] rounded-lg text-sm text-[#101828] placeholder-[#98A2B3] focus:outline-none focus:ring-2 focus:ring-[#062072] focus:border-[#062072] transition-colors"
            placeholder="Ulangi password">
    </div>

    <button type="submit" id="btn-register"
        class="w-full h-11 px-4 bg-[#062072] hover:bg-[#04184F] active:scale-[0.98] text-white font-semibold text-sm rounded-lg transition-all flex items-center justify-center shadow-xs cursor-pointer">
        Daftar Customer
    </button>
</form>

<p class="mt-5 text-center text-sm text-[#667085] m-0">
    Sudah punya akun?
    <a href="{{ route('login') }}" class="text-[#062072] font-semibold hover:underline">Login di sini</a>
</p>
@endsection
