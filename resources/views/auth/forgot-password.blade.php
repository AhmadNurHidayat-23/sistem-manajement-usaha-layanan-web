@extends('layouts.auth', ['title' => 'Lupa Password'])

@section('content')
<div class="flex items-center gap-3 mb-5">
    <div class="w-10 h-10 rounded-xl bg-[#DCE8FF] flex items-center justify-center shrink-0">
        <svg class="w-5 h-5 text-[#062072]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>
    <div>
        <h2 class="text-xl font-bold text-[#101828] m-0">Lupa Password</h2>
        <p class="text-xs text-[#667085] m-0">Kami akan mengirimkan link reset ke email Anda.</p>
    </div>
</div>

@if (session('status'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm">
        @foreach ($errors->all() as $error)
            <p class="m-0">{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
    @csrf

    <div>
        <label for="email" class="block text-sm font-medium text-[#101828] mb-1">
            Alamat Email
        </label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full px-3 py-2.5 bg-white border border-[#E4E7EC] rounded-lg text-sm text-[#101828] placeholder-[#667085] focus:outline-none focus:ring-2 focus:ring-[#062072]"
            placeholder="nama@email.com">
        @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>

    <button type="submit" id="btn-kirim-reset"
        class="w-full py-2.5 px-4 bg-[#062072] hover:bg-[#04184F] text-white font-semibold text-sm rounded-lg transition-colors flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        Kirim Link Reset Password
    </button>
</form>

<p class="mt-6 text-center text-sm text-[#667085]">
    Ingat password?
    <a href="{{ route('login') }}" class="text-[#062072] font-medium hover:underline">Kembali ke Login</a>
</p>
@endsection
