<?php

namespace App\Modules\BookingAntrian\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Service;
use App\Modules\BookingAntrian\Requests\StorePesananRequest;
use App\Modules\BookingAntrian\Services\BookingService;
use App\Modules\MembershipPromo\Services\PromoService;
use App\Services\NotifikasiService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PelangganPesananController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected PromoService $promoService,
        protected NotifikasiService $notifikasiService,
    ) {}

    /**
     * Daftar pesanan pelanggan.
     */
    public function orders(Request $request): View
    {
        $filters = $request->only(['status']);
        $orders = $this->bookingService->getOrdersForUser($request->user(), $filters);

        return view('modules.booking-antrian.pelanggan.pesanan.index', compact('orders', 'filters'));
    }

    /**
     * Form pesanan pelanggan.
     */
    public function createOrder(Request $request): View
    {
        $branches = $this->bookingService->getActiveBranches('drop_off');

        $selectedBranchId = $request->query('branch_id');
        $selectedServiceId = $request->query('service_id');

        // Jika branch_id belum ada tetapi service_id ada, infer branch_id dari service jika aktif
        if (! empty($selectedServiceId) && empty($selectedBranchId)) {
            $service = Service::where('is_active', true)->find($selectedServiceId);
            if ($service && $service->branch_id) {
                $selectedBranchId = (string) $service->branch_id;
            } else {
                $selectedServiceId = null;
            }
        }

        // Ambil layanan aktif yang difilter STRICT berdasarkan cabang jika cabang telah dipilih
        // Jika belum ada cabang yang dipilih, jangan tampilkan daftar layanan
        $services = ! empty($selectedBranchId)
            ? $this->bookingService->getActiveServices((int) $selectedBranchId)
            : collect();

        // Validasi ketersediaan service pada branch yang dipilih
        $selectedService = null;
        if (! empty($selectedServiceId) && ! empty($selectedBranchId)) {
            $selectedService = $services->first(function (Service $s) use ($selectedServiceId, $selectedBranchId) {
                return (int) $s->id === (int) $selectedServiceId
                    && (int) $s->branch_id === (int) $selectedBranchId;
            });

            // Jika service tidak tersedia pada branch yang dipilih, reset service
            if (! $selectedService) {
                $selectedServiceId = null;
            }
        } else {
            $selectedServiceId = null;
        }

        return view('modules.booking-antrian.pelanggan.pesanan.create', compact('branches', 'services', 'selectedServiceId', 'selectedBranchId', 'selectedService'));
    }

    /**
     * API Layanan aktif berdasarkan cabang untuk dropdown dinamis.
     */
    public function branchServices(Request $request): JsonResponse
    {
        $branchId = $request->query('branch_id');
        if (! $branchId) {
            return response()->json([]);
        }

        $services = $this->bookingService->getActiveServices((int) $branchId);

        $data = $services->map(fn (Service $service) => [
            'id' => (string) $service->id,
            'branch_id' => (string) $service->branch_id,
            'name' => $service->name,
            'price' => (float) $service->price,
            'is_weight' => $service->isWeightBased() ? '1' : '0',
            'unit' => (string) ($service->unit ?? ''),
            'duration' => (int) ($service->estimated_duration ?? 0),
            'duration_formatted' => $service->formatted_duration,
            'label' => $service->name.' — Rp'.number_format($service->price, 0, ',', '.').($service->isWeightBased() ? ' / kg' : '').' — '.$service->formatted_duration,
        ]);

        return response()->json($data);
    }

    /**
     * Konfirmasi pesanan pelanggan.
     */
    public function confirmOrder(Request $request): View|RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'service_id' => [
                'required',
                'exists:services,id',
                Rule::exists('services', 'id')->where(function ($query) use ($request) {
                    $query->where('is_active', true)
                        ->where('branch_id', $request->input('branch_id'));
                }),
            ],
            'estimated_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'pickup_option' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'promo_code' => ['nullable', 'string', 'max:50'],
        ], [
            'service_id.exists' => 'Layanan yang dipilih tidak tersedia pada cabang tersebut.',
        ]);

        $branch = Branch::findOrFail($data['branch_id']);
        if (! in_array($branch->service_mode, ['drop_off', 'both'], true)) {
            return back()->withErrors(['branch_id' => 'Cabang yang dipilih tidak melayani pesanan drop-off.'])->withInput();
        }

        $service = Service::with('category')->findOrFail($data['service_id']);
        $isWeightBased = $service->isWeightBased();

        $quantity = ! empty($data['estimated_quantity']) ? (float) $data['estimated_quantity'] : null;

        if ($isWeightBased) {
            // Untuk layanan berbasis berat: harga awal merupakan harga per kg
            // Total akhir ditentukan setelah penimbangan oleh petugas
            $estimatedPrice = (float) $service->price;
        } else {
            // Untuk layanan fixed-price: harga layanan existing
            $estimatedPrice = (float) $service->price;
            $quantity = 1;
        }

        $estimatedDuration = $service->estimated_duration ?? 60;

        // Validate promo if provided
        $promoResult = null;
        if (! empty($data['promo_code'])) {
            $promoResult = $this->promoService->validatePromo(
                $data['promo_code'],
                $estimatedPrice,
                (int) $data['branch_id'],
                $request->user()
            );
        }

        return view('modules.booking-antrian.pelanggan.pesanan.confirm', compact(
            'data',
            'service',
            'branch',
            'estimatedPrice',
            'estimatedDuration',
            'promoResult',
            'isWeightBased',
            'quantity'
        ));
    }

    /**
     * Simpan pesanan baru.
     */
    public function storeOrder(StorePesananRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $service = Service::with('category')->find($validated['service_id']);

        if ($service && ! $service->isWeightBased() && empty($validated['estimated_quantity'])) {
            $validated['estimated_quantity'] = 1;
        }

        $order = $this->bookingService->createOrder($request->user(), $validated);

        rescue(fn () => $this->notifikasiService->notifikasiOrderBaru($order->fresh()));

        $request->session()->put('selected_order_id', $order->id);

        return redirect()->route('customer.pesanan.detail')
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    /**
     * Pilih referensi pesanan dan simpan ke session secara aman.
     */
    public function selectOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $this->authorize('view', $order);

        $request->session()->put('selected_order_id', $order->id);

        return redirect()->route('customer.pesanan.detail');
    }

    /**
     * Tampilkan detail pesanan pelanggan dari session terpilih.
     */
    public function showDetail(Request $request): View|RedirectResponse
    {
        $orderId = $request->session()->get('selected_order_id');

        if (! $orderId) {
            return redirect()->route('customer.pesanan.index')
                ->with('info', 'Silakan pilih pesanan terlebih dahulu.');
        }

        $order = Order::find($orderId);

        if (! $order) {
            $request->session()->forget('selected_order_id');

            return redirect()->route('customer.pesanan.index')
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        $this->authorize('view', $order);

        $order->load(['branch', 'service.category', 'user', 'details.service', 'transaction.payments', 'transaction.ulasan']);

        return view('modules.booking-antrian.pelanggan.pesanan.show', compact('order'));
    }

    /**
     * Detail pesanan pelanggan via direct link / fallback aman.
     */
    public function showOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        $request->session()->put('selected_order_id', $order->id);

        return redirect()->route('customer.pesanan.detail');
    }

    /**
     * Cancel a pending order.
     */
    public function cancelOrder(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        try {
            $this->bookingService->cancelOrder($request->user(), $order);

            rescue(fn () => $this->notifikasiService->notifikasiStatusOrder($order->fresh()));

            return redirect()->route('customer.pesanan.index')
                ->with('success', 'Order berhasil dibatalkan.');
        } catch (\RuntimeException|AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
