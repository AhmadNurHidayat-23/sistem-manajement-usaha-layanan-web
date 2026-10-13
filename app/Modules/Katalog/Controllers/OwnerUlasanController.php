<?php

namespace App\Modules\Katalog\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ulasan;
use App\Modules\Katalog\Requests\StoreUlasanRequest;
use App\Modules\Katalog\Requests\UpdateUlasanRequest;
use App\Modules\Katalog\Services\UlasanService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerUlasanController extends Controller
{
    public function __construct(protected UlasanService $ulasanService) {}

    /**
     * Daftar semua ulasan.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['service_id', 'rating']);
        $ulasans = $this->ulasanService->getUlasanList($request->user(), $filters);

        return view('modules.katalog.owner.ulasan.index', compact('ulasans', 'filters'));
    }

    /**
     * Form tulis ulasan.
     * Requires transaction_id query param.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $transactionId = $request->query('transaction_id');

        if (! $transactionId) {
            return redirect()->route('bookings.index')->with('error', 'Pilih transaksi terlebih dahulu.');
        }

        try {
            $transaction = $this->ulasanService->resolveCustomerTransaction($request->user(), (int) $transactionId);
        } catch (AuthorizationException $e) {
            return redirect()->route('bookings.index')->with('error', $e->getMessage());
        }

        if ($transaction->ulasan !== null) {
            return redirect()->route('ulasan.edit', $transaction->ulasan)
                ->with('info', 'Transaksi ini sudah memiliki ulasan. Anda dapat mengeditnya di bawah.');
        }

        return view('modules.katalog.owner.ulasan.create', compact('transaction'));
    }

    /**
     * Simpan review baru.
     */
    public function store(StoreUlasanRequest $request): RedirectResponse
    {
        try {
            $this->ulasanService->createUlasan($request->user(), $request->validated());
        } catch (AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.index')->with('success', 'Ulasan berhasil dikirim. Terima kasih!');
    }

    /**
     * Form edit ulasan.
     */
    public function edit(Request $request, Ulasan $ulasan): View|RedirectResponse
    {
        if ($ulasan->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki izin mengubah ulasan ini.');
        }

        $ulasan->load(['transaction', 'service', 'employee']);

        return view('modules.katalog.owner.ulasan.edit', compact('ulasan'));
    }

    /**
     * Perbarui ulasan.
     */
    public function update(UpdateUlasanRequest $request, Ulasan $ulasan): RedirectResponse
    {
        try {
            $this->ulasanService->updateUlasan($request->user(), $ulasan, $request->validated());
        } catch (AuthorizationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.index')->with('success', 'Ulasan berhasil diperbarui.');
    }

    /**
     * Ulasan spesifik layanan.
     */
    public function forService(Request $request, int $serviceId): View
    {
        $ulasans = $this->ulasanService->getUlasanForService($serviceId);
        $stats = $this->ulasanService->getServiceRatingStats($serviceId);

        return view('modules.katalog.owner.ulasan.by-service', compact('ulasans', 'stats', 'serviceId'));
    }
}
