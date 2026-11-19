<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\BookingStatus;
use App\Enums\StatusPesanan;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PelangganAktivitasController extends Controller
{
    /**
     * Tampilkan riwayat seluruh aktivitas pelanggan (Booking & Pesanan Drop-off).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Aktivitas Booking (Appointment)
        $bookings = Booking::where('user_id', $user->id)
            ->with(['branch', 'service'])
            ->latest('booking_date')
            ->latest('id')
            ->get()
            ->map(function ($booking) {
                $statusVal = $booking->status instanceof BookingStatus ? $booking->status->value : (string) $booking->status;
                $statusLabel = $booking->status instanceof BookingStatus ? $booking->status->label() : ($booking->status_label ?? ucfirst($statusVal));

                return (object) [
                    'type' => 'appointment',
                    'type_label' => __('public.catalog.appointment'),
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

        // 2. Aktivitas Pesanan Drop-off
        $orders = Order::where('user_id', $user->id)
            ->with(['branch', 'service', 'transaction'])
            ->latest()
            ->get()
            ->map(function ($order) {
                $statusVal = $order->status instanceof StatusPesanan ? $order->status->value : (string) $order->status;
                $statusLabel = $order->status instanceof StatusPesanan ? $order->status->label() : ($order->status_label ?? ucfirst($statusVal));
                $paymentStatus = $order->transaction?->status?->value ?? ($order->payment_status ?? 'unpaid');
                $typeLabel = ($order->pickup_type ?? null) === 'pickup_delivery'
                    ? 'Antar-Jemput'
                    : __('public.catalog.drop_off');

                return (object) [
                    'type' => 'pesanan',
                    'type_label' => $typeLabel,
                    'id' => $order->id,
                    'service_name' => $order->service?->name ?? 'Layanan',
                    'branch_name' => $order->branch?->display_name ?? ($order->branch?->name ?? 'Cabang'),
                    'date' => $order->created_at ? $order->created_at->format('d M Y • H:i') : '-',
                    'raw_date' => $order->created_at?->timestamp ?? 0,
                    'status' => $statusVal,
                    'status_label' => $statusLabel,
                    'payment_status' => $paymentStatus,
                    'payment_label' => $paymentStatus === 'paid' ? __('dashboard.status.paid') : __('dashboard.status.unpaid'),
                    'url' => route('customer.pesanan.show', $order),
                ];
            });

        // 3. Gabungkan dan urutkan aktivitas berdasarkan tanggal terbaru
        $activities = $bookings->concat($orders)->sortByDesc('raw_date')->values();

        return view('pelanggan.aktivitas.index', compact('activities'));
    }
}
