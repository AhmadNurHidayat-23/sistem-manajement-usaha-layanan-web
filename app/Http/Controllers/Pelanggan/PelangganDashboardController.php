<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\BookingStatus;
use App\Enums\PromotionStatus;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganDashboardController extends Controller
{
    /**
     * Dashboard pelanggan yang disederhanakan dan terintegrasi.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 3 Usaha (masing-masing satu kali) beserta layanan aktif
        $branches = Branch::where('is_active', true)
            ->with(['services' => function ($query) {
                $query->where('is_active', true)->with('category')->orderBy('name');
            }])
            ->orderBy('id')
            ->get();

        // Aktivitas gabungan: Booking (Appointment) & Order (Drop-off)
        $bookings = Booking::where('user_id', $user->id)
            ->with(['branch', 'service'])
            ->latest('booking_date')
            ->latest('id')
            ->take(10)
            ->get()
            ->map(function ($booking) {
                $statusVal = $booking->status instanceof BookingStatus ? $booking->status->value : (string) $booking->status;
                $statusLabel = $booking->status instanceof BookingStatus ? $booking->status->label() : ($booking->status_label ?? ucfirst($statusVal));

                return (object) [
                    'type' => 'appointment',
                    'type_label' => 'Appointment',
                    'id' => $booking->id,
                    'service_name' => $booking->service?->name ?? 'Layanan',
                    'branch_name' => $booking->branch?->display_name ?? ($booking->branch?->name ?? 'Cabang'),
                    'date' => $booking->booking_date ? Carbon::parse($booking->booking_date)->format('d M Y').($booking->booking_time ? ' • '.substr($booking->booking_time, 0, 5) : '') : '-',
                    'raw_date' => $booking->booking_date ? Carbon::parse($booking->booking_date)->timestamp : $booking->created_at?->timestamp,
                    'status' => $statusVal,
                    'status_label' => $statusLabel,
                    'payment_status' => null,
                    'payment_label' => null,
                    'url' => route('customer.booking.show', $booking),
                ];
            });

        $orders = Order::where('user_id', $user->id)
            ->with(['branch', 'service', 'transaction'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) {
                $statusVal = $order->status instanceof StatusPesanan ? $order->status->value : (string) $order->status;
                $statusLabel = $order->status instanceof StatusPesanan ? $order->status->label() : ($order->status_label ?? ucfirst($statusVal));
                $paymentStatus = $order->transaction?->status?->value ?? ($order->payment_status ?? 'unpaid');

                return (object) [
                    'type' => 'pesanan',
                    'type_label' => 'Drop-off',
                    'id' => $order->id,
                    'service_name' => $order->service?->name ?? 'Layanan',
                    'branch_name' => $order->branch?->display_name ?? ($order->branch?->name ?? 'Cabang'),
                    'date' => $order->created_at ? $order->created_at->format('d M Y • H:i') : '-',
                    'raw_date' => $order->created_at?->timestamp ?? 0,
                    'status' => $statusVal,
                    'status_label' => $statusLabel,
                    'payment_status' => $paymentStatus,
                    'payment_label' => $paymentStatus === 'paid' ? 'Lunas' : 'Belum Bayar',
                    'url' => route('customer.pesanan.show', $order),
                ];
            });

        $activities = $bookings->concat($orders)->sortByDesc('raw_date')->values();

        // Membership pelanggan
        $membership = $user->membership;

        // Promo aktif yang relevan
        $activePromos = Promotion::where('status', PromotionStatus::Active)
            ->where('start_date', '<=', Carbon::today())
            ->where('end_date', '>=', Carbon::today())
            ->latest('id')
            ->take(2)
            ->get();

        return view('pelanggan.dashboard', compact(
            'branches',
            'activities',
            'membership',
            'activePromos',
        ));
    }
}
