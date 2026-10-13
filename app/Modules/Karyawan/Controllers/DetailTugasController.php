<?php

namespace App\Modules\Karyawan\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Employee;
use App\Models\Order;
use App\Modules\BookingAntrian\Services\AntrianLayananService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class DetailTugasController extends Controller
{
    public function __construct(
        protected AntrianLayananService $antrianService
    ) {}

    /**
     * Tampilkan detail tugas berdasarkan public identifier UUID yang unik dan stabil.
     */
    public function showTask(Request $request, string $identifier): View|RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->first();

        if (! $employee && ! in_array($user->role?->name, ['owner', 'manager'], true)) {
            abort(403, 'Akses khusus untuk karyawan aktif.');
        }

        // 1. Coba cari Booking (Appointment) berdasarkan UUID atau ID numerik
        $booking = Booking::with(['branch', 'service.category', 'employee', 'user', 'details.service'])
            ->where('uuid', $identifier)
            ->first();

        if (! $booking && is_numeric($identifier)) {
            $booking = Booking::with(['branch', 'service.category', 'employee', 'user', 'details.service'])
                ->find($identifier);
        }

        if ($booking) {
            if ($employee && (int) $booking->employee_id !== (int) $employee->id && ! in_array($user->role?->name, ['owner', 'manager'], true)) {
                abort(403, 'Anda tidak memiliki hak akses ke tugas karyawan lain.');
            }

            return view('modules.booking-antrian.internal.booking.show', [
                'booking' => $booking,
            ]);
        }

        // 2. Coba cari Order (Drop-off) berdasarkan UUID atau ID numerik
        $order = Order::with(['branch', 'service.category', 'employee', 'user', 'details.service'])
            ->where('uuid', $identifier)
            ->first();

        if (! $order && is_numeric($identifier)) {
            $order = Order::with(['branch', 'service.category', 'employee', 'user', 'details.service'])
                ->find($identifier);
        }

        if ($order) {
            if ($employee && (int) $order->employee_id !== (int) $employee->id && ! in_array($user->role?->name, ['owner', 'manager'], true)) {
                abort(403, 'Anda tidak memiliki hak akses ke tugas karyawan lain.');
            }

            return view('modules.booking-antrian.manager.show', [
                'order' => $order,
            ]);
        }

        abort(404, 'Tugas tidak ditemukan.');
    }

    /**
     * Tampilkan detail tugas legacy dengan token query.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee ?: Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            abort(403, 'Akses khusus untuk karyawan aktif.');
        }

        $ref = $request->query('ref');
        $resolvedModel = null;

        if (! empty($ref)) {
            try {
                $decrypted = Crypt::decryptString($ref);
                [$type, $id] = explode(':', $decrypted, 2);

                if ($type === 'booking') {
                    $resolvedModel = Booking::where('employee_id', $employee->id)->find($id);
                } elseif ($type === 'order') {
                    $resolvedModel = Order::where('employee_id', $employee->id)->find($id);
                }
            } catch (\Throwable) {
                return redirect()->route('queue.index')->with('error', 'Tautan tugas tidak valid.');
            }
        }

        if ($resolvedModel) {
            $viewName = ($type ?? null) === 'order' || $resolvedModel instanceof Order
                ? 'modules.booking-antrian.manager.show'
                : 'modules.booking-antrian.internal.booking.show';
            $varName = $resolvedModel instanceof Order ? 'order' : 'booking';

            return view($viewName, [$varName => $resolvedModel]);
        }

        $today = Carbon::today()->toDateString();
        $tasks = $this->antrianService->getUnifiedDailyTasks($user, $today);

        if ($tasks->isNotEmpty()) {
            $firstTask = $tasks->first();
            $model = $firstTask['model'] ?? null;
            if ($model) {
                $viewName = $model instanceof Order
                    ? 'modules.booking-antrian.manager.show'
                    : 'modules.booking-antrian.internal.booking.show';
                $varName = $model instanceof Order ? 'order' : 'booking';

                return view($viewName, [$varName => $model]);
            }
        }

        return redirect()->route('queue.index')->with('info', 'Pilih salah satu tugas dari daftar antrean Anda.');
    }

    /**
     * Helper untuk membuat token referensi tugas yang aman.
     */
    public static function makeTaskToken(string $type, int|string $id): string
    {
        return Crypt::encryptString("{$type}:{$id}");
    }
}
