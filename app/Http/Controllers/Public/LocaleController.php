<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Ganti bahasa antarmuka antara Bahasa Indonesia (ID) dan Bahasa Inggris (EN).
     */
    public function toggle(Request $request): JsonResponse|RedirectResponse
    {
        $currentLocale = $request->session()->get('locale')
            ?? $request->cookie('simaul_locale')
            ?? config('app.locale', 'id');

        $newLocale = ($currentLocale === 'en') ? 'id' : 'en';

        $request->session()->put('locale', $newLocale);

        $cookie = cookie('simaul_locale', $newLocale, 60 * 24 * 365, null, null, false, false);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'locale' => $newLocale,
                'label' => $newLocale === 'en' ? 'EN 🇬🇧' : 'ID 🇮🇩',
            ])->withCookie($cookie);
        }

        return redirect()->back()->withCookie($cookie);
    }
}
