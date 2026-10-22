@extends('layouts.pelanggan', ['title' => 'Profil Saya'])

@php
    $cleanUserName = preg_replace('/\s*\(.*?\)\s*$/', '', $user->name);
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    {{-- Header & Navigasi --}}
    <div>
        <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#667085] hover:text-[#062072] transition-colors no-underline mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Beranda
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-[#101828] m-0 tracking-tight">Profil Saya</h2>
                <p class="text-xs text-[#667085] mt-1 m-0">Kelola dan perbarui data profil akun pelanggan Anda</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-[#EBF2FE] text-[#062072] border border-[#062072]/20 self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-[#062072]"></span>
                Pelanggan Terdaftar
            </span>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2.5 shadow-2xs">
            <svg class="w-5 h-5 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2.5 shadow-2xs">
            <svg class="w-5 h-5 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm shadow-2xs">
            <div class="flex items-center gap-2 font-semibold mb-1 text-rose-900">
                <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Mohon periksa kesalahan input berikut:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 m-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Kartu Ringkasan Akun --}}
        @include('pelanggan.profil.partials.account-summary')

        {{-- Kolom Kanan: Form Edit Profil --}}
        @include('pelanggan.profil.partials.edit-profile-form')
    </div>
</div>
@endsection
