<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    /**
     * Riwayat notifikasi.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['channel', 'type', 'status', 'q']);

        $logs = NotificationLog::with('user')
            ->when($filters['channel'] ?? null, fn ($q, $v) => $q->where('channel', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['q'] ?? null, fn ($q, $v) => $q->where(function ($sub) use ($v) {
                $sub->where('recipient', 'like', "%{$v}%")
                    ->orWhere('message', 'like', "%{$v}%");
            }))
            ->orderByDesc('created_at')
            ->paginate(30);

        $typeList = NotificationLog::distinct()->orderBy('type')->pluck('type');
        $stats = [
            'sent' => NotificationLog::where('status', 'sent')->count(),
            'failed' => NotificationLog::where('status', 'failed')->count(),
            'pending' => NotificationLog::where('status', 'pending')->count(),
        ];

        return view('shared.notifikasi.index', compact('logs', 'filters', 'typeList', 'stats'));
    }
}
