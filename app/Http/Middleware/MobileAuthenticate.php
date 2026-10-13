<?php

namespace App\Http\Middleware;

use App\Models\MobileAccessToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MobileAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token autentikasi tidak ditemukan. Silakan login kembali.',
            ], 401);
        }

        $tokenRecord = MobileAccessToken::findActiveToken($bearerToken);

        if (! $tokenRecord || ! $tokenRecord->user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login telah kedaluwarsa atau tidak valid.',
            ], 401);
        }

        $user = $tokenRecord->user;
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        // Validasi role jika dispesifikasikan (misal customer atau employee)
        if ($requiredRole !== null) {
            $userRole = $user->role?->name;
            if ($userRole !== $requiredRole) {
                return response()->json([
                    'success' => false,
                    'message' => "Akses ditolak. Fitur ini hanya untuk pengguna dengan role {$requiredRole}.",
                ], 403);
            }
        }

        return $next($request);
    }
}
