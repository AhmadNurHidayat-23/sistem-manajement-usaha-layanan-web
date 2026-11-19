<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale')
            ?? $request->cookie('simaul_locale')
            ?? config('app.locale', 'id');

        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        App::setLocale($locale);

        $response = $next($request);

        // Pastikan cookie simaul_locale selalu sinkron jika belum ada atau berbeda
        if ($request->cookie('simaul_locale') !== $locale) {
            $response->headers->setCookie(cookie('simaul_locale', $locale, 60 * 24 * 365, null, null, false, false));
        }

        return $response;
    }
}
