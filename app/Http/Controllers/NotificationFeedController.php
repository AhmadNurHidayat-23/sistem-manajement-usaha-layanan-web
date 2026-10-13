<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationFeedController extends Controller
{
    /**
     * Dapatkan feed notifikasi dan jumlah notifikasi belum dibaca untuk lonceng header.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['unread_count' => 0, 'notifications' => []], 401);
        }

        $unreadCount = $user->unreadNotifications()->count();

        $notifications = $user->notifications()
            ->latest()
            ->take(15)
            ->get()
            ->map(function ($n) {
                $data = $n->data;

                return [
                    'id' => $n->id,
                    'title' => $data['title'] ?? 'Notifikasi SIMAUL',
                    'message' => $data['message'] ?? '',
                    'category' => $data['category'] ?? 'info',
                    'status_level' => $data['status_level'] ?? 'info',
                    'action_url' => $data['action_url'] ?? null,
                    'action_label' => $data['action_label'] ?? null,
                    'is_read' => ! is_null($n->read_at),
                    'created_at_human' => $n->created_at?->diffForHumans() ?? 'Baru saja',
                ];
            });

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Tandai sebuah notifikasi telah dibaca.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()?->notifications()->where('id', $id)->first();
        if (! $notification) {
            return response()->json(['error' => 'Notifikasi tidak ditemukan'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Tandai semua notifikasi pengguna saat ini telah dibaca.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json(['success' => true]);
    }
}
