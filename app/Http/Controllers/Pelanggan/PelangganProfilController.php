<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganProfilController extends Controller
{
    /**
     * Tampilkan halaman profil pelanggan.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['membership', 'role']);

        $totalBookings = $user->bookings()->count();
        $totalOrders = $user->orders()->count();

        return view('pelanggan.profil.index', compact('user', 'totalBookings', 'totalOrders'));
    }

    /**
     * Perbarui data nama dan nomor telepon profil pelanggan.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap tidak boleh lebih dari 255 karakter.',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 digit.',
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan angka dan karakter (+, -, space).',
        ]);

        $user = $request->user();
        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('customer.profile')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }
}
