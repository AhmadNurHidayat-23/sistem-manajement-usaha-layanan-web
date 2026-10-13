<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\MobileAccessToken;
use App\Models\User;
use App\Services\AktivitasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        protected AktivitasService $aktivitasService,
    ) {}

    /**
     * Login pengguna untuk aplikasi mobile.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::with(['role', 'branches', 'employee'])->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi yang Anda masukkan salah.',
            ], 422);
        }

        $roleName = $user->role?->name;

        // Validasi role: Hanya Pelanggan dan Karyawan yang diizinkan di mobile
        if ($roleName !== UserRole::Customer->value && $roleName !== UserRole::Employee->value) {
            return response()->json([
                'success' => false,
                'message' => 'Aplikasi SIMAUL Mobile saat ini hanya tersedia untuk Pelanggan dan Karyawan/Petugas. Silakan gunakan dashboard web.',
                'role' => $roleName,
            ], 403);
        }

        $tokenData = MobileAccessToken::generateForUser(
            $user,
            $request->input('device_name', 'SIMAUL Mobile App')
        );

        $this->aktivitasService->catat(
            user: $user,
            aktivitas: 'Login Mobile',
            modul: 'mobile-auth',
            keterangan: "Login mobile berhasil sebagai {$roleName}",
            ipAddress: $request->ip(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $tokenData['token'],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $roleName,
                    'employee_id' => $user->employee?->id,
                    'branch_id' => $user->employee?->branch_id ?? $user->branches->first()?->id,
                    'branch_name' => $user->employee?->branch?->name ?? $user->branches->first()?->name,
                ],
            ],
        ]);
    }

    /**
     * Ambil informasi profil pengguna login saat ini.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadMissing(['role', 'employee.branch', 'branches', 'membership.loyaltyPoint']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role?->name,
                'employee' => $user->employee ? [
                    'id' => $user->employee->id,
                    'branch_id' => $user->employee->branch_id,
                    'branch_name' => $user->employee->branch?->name,
                    'status' => $user->employee->status,
                ] : null,
                'membership' => $user->membership ? [
                    'member_code' => $user->membership->member_code,
                    'status' => $user->membership->status?->value ?? 'active',
                    'points' => $user->membership->loyaltyPoint?->points ?? 0,
                ] : null,
            ],
        ]);
    }

    /**
     * Logout dan hapus token aktif.
     */
    public function logout(Request $request): JsonResponse
    {
        $bearerToken = $request->bearerToken();

        if ($bearerToken) {
            $hashedToken = hash('sha256', $bearerToken);
            MobileAccessToken::where('token', $hashedToken)->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Sesi telah diakhiri.',
        ]);
    }
}
