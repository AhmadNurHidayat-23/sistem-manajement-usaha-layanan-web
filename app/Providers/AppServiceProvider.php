<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Transaction;
use App\Modules\BookingAntrian\Policies\BookingPolicy;
use App\Modules\BookingAntrian\Policies\PesananPolicy;
use App\Modules\KasirPOS\Services\MidtransPaymentGateway;
use App\Modules\KasirPOS\Services\PaymentGatewayInterface;
use App\Modules\KasirPOS\Services\SandboxPaymentGateway;
use App\Observers\BookingObserver;
use App\Observers\OrderObserver;
use App\Observers\TransactionObserver;
use App\Policies\TransaksiPolicy;
use Carbon\Carbon;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * Panduan integrasi gateway produksi:
     * 1. Create a class implementing PaymentGatewayInterface (e.g. MidtransGateway)

     * 3. Configure credentials in .env (PAYMENT_GATEWAY_KEY, PAYMENT_GATEWAY_SECRET, etc.)
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            $driver = config('services.payment_gateway.driver', 'sandbox');

            return match ($driver) {
                'midtrans' => $app->make(MidtransPaymentGateway::class),
                default => $app->make(SandboxPaymentGateway::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        config(['app.locale' => 'id']);

        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Order::class, PesananPolicy::class);
        Gate::policy(Transaction::class, TransaksiPolicy::class);

        Booking::observe(BookingObserver::class);
        Order::observe(OrderObserver::class);
        Transaction::observe(TransactionObserver::class);

        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            $user = $request->user();
            $roleName = $user?->role?->name;

            return match ($roleName) {
                UserRole::Owner->value => route('owner.dashboard'),
                UserRole::Manager->value => route('manager.dashboard'),
                UserRole::Cashier->value => route('cashier.dashboard'),
                UserRole::Employee->value => route('employee.dashboard'),
                UserRole::Customer->value => route('customer.dashboard'),
                default => route('customer.dashboard'),
            };
        });

        // Kompatibilitas route name legacy payment.gateway.waiting -> kasir.pembayaran
        app('url')->resolveMissingNamedRoutesUsing(function ($name, $parameters, $absolute) {
            if ($name === 'payment.gateway.waiting') {
                return route('kasir.pembayaran', $parameters, $absolute);
            }
        });
    }
}
