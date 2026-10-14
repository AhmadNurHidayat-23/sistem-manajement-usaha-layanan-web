<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AktivitasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(protected AktivitasService $aktivitasService) {}

    /**
     * Halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $normalizedEmail = strtolower($credentials['email']);
        $demoAliases = [
            'owner@example.com' => 'owner@simaul.com',
            'manager@example.com' => 'manajer@simaul.com',
            'manager@simaul.com' => 'manajer@simaul.com',
            'manager.barbershop@simaul.com' => 'manajer@simaul.com',
            'manajer.barbershop@simaul.com' => 'manajer@simaul.com',
            'manager.selatan@example.com' => 'manajer.laundry@simaul.com',
            'manager.laundry@simaul.com' => 'manajer.laundry@simaul.com',
            'manager.barat@example.com' => 'manajer.autocare@simaul.com',
            'manager.autocare@simaul.com' => 'manajer.autocare@simaul.com',
            'cashier@example.com' => 'kasir@simaul.com',
            'cashier.selatan@example.com' => 'kasir.laundry@simaul.com',
            'cashier.barat@example.com' => 'kasir.autocare@simaul.com',
            'customer@example.com' => 'pelanggan@simaul.com',
            'annisa@example.com' => 'pelanggan.annisa@simaul.com',
            'maul@pelanggan.com' => 'pelanggan.maul@simaul.com',
            'pelanggan@maul.com' => 'pelanggan.ahmad@simaul.com',
            'employee@example.com' => 'karyawan@simaul.com',
            'employee.selatan@example.com' => 'karyawan.laundry@simaul.com',
            'employee.barat@example.com' => 'karyawan.autocare@simaul.com',
            'karyawan.barbershop@simaul.com' => 'karyawan@simaul.com',
            'dedi.barbershop@simaul.com' => 'karyawan@simaul.com',
            'agus.laundry@simaul.com' => 'karyawan.laundry@simaul.com',
            'fajar.autocare@simaul.com' => 'karyawan.autocare@simaul.com',
        ];

        if (isset($demoAliases[$normalizedEmail]) && ! User::where('email', $normalizedEmail)->exists()) {
            $credentials['email'] = $demoAliases[$normalizedEmail];
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        $this->aktivitasService->catat(
            user: $user,
            aktivitas: 'Login',
            modul: 'auth',
            keterangan: "Login berhasil sebagai {$user->role?->name}",
            ipAddress: $request->ip(),
        );

        $roleName = $user->role?->name;
        $targetUrl = $this->getRedirectUrlForRole($roleName);

        // Pelanggan diarahkan ke intended customer URL (misal: checkout/pesanan) atau area Pesanan Saya
        if ($roleName === UserRole::Customer->value) {
            $intended = $request->session()->pull('url.intended');

            if ($intended) {
                $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '/';
                // Jika intended adalah landing page root ('/') atau halaman login/register,
                // langsung arahkan ke Pesanan Saya agar terasa jelas berada di area personal
                if ($intendedPath === '/' || $intendedPath === '/login' || $intendedPath === '/register') {
                    return redirect()->to($targetUrl);
                }

                return redirect()->to($intended);
            }

            return redirect()->to($targetUrl);
        }

        // Role internal (Owner, Manager, Kasir, Karyawan):
        // Tidak boleh diarahkan ke landing page '/' atau area publik/customer.
        $intended = $request->session()->pull('url.intended');

        if ($intended) {
            $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '/';
            $isPublicOrCustomer = $intendedPath === '/'
                || str_starts_with($intendedPath, '/customer')
                || str_starts_with($intendedPath, '/layanan')
                || str_starts_with($intendedPath, '/promo')
                || str_starts_with($intendedPath, '/login')
                || str_starts_with($intendedPath, '/register');

            if (! $isPublicOrCustomer) {
                $isAllowed = match ($roleName) {
                    UserRole::Owner->value => str_starts_with($intendedPath, '/owner') || str_starts_with($intendedPath, '/aktivitas') || str_starts_with($intendedPath, '/notifikasi') || str_starts_with($intendedPath, '/pos') || str_starts_with($intendedPath, '/laporan') || str_starts_with($intendedPath, '/karyawan') || str_starts_with($intendedPath, '/branches') || str_starts_with($intendedPath, '/services'),
                    UserRole::Manager->value => str_starts_with($intendedPath, '/manager') || str_starts_with($intendedPath, '/aktivitas') || str_starts_with($intendedPath, '/notifikasi') || str_starts_with($intendedPath, '/pos') || str_starts_with($intendedPath, '/laporan') || str_starts_with($intendedPath, '/inventory') || str_starts_with($intendedPath, '/branches') || str_starts_with($intendedPath, '/services'),
                    UserRole::Cashier->value => str_starts_with($intendedPath, '/cashier') || str_starts_with($intendedPath, '/kasir') || str_starts_with($intendedPath, '/pos') || str_starts_with($intendedPath, '/members'),
                    UserRole::Employee->value => str_starts_with($intendedPath, '/employee') || str_starts_with($intendedPath, '/karyawan'),
                    default => false,
                };

                if ($isAllowed) {
                    return redirect()->to($intended);
                }
            }
        }

        return redirect()->to($targetUrl);
    }

    /**
     * URL redirect sesuai role pengguna.
     */
    protected function getRedirectUrlForRole(?string $roleName): string
    {
        return match ($roleName) {
            UserRole::Owner->value => route('owner.dashboard'),
            UserRole::Manager->value => route('manager.dashboard'),
            UserRole::Cashier->value => route('cashier.dashboard'),
            UserRole::Employee->value => route('employee.dashboard'),
            UserRole::Customer->value => route('customer.dashboard'),
            default => route('customer.dashboard'),
        };
    }

    /**
     * Logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $this->aktivitasService->catat(
            user: $user,
            aktivitas: 'Logout',
            modul: 'auth',
            keterangan: 'Sesi pengguna diakhiri',
            ipAddress: $request->ip(),
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
