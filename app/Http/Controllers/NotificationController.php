<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = AdminNotification::forUser($user->id)->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $paginated = $query->paginate($perPage);

        $unreadCount = AdminNotification::forUser($user->id)->unread()->count();

        return response()->json([
            'notifications' => $paginated->items(),
            'unread_count' => $unreadCount,
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function unreadCount(Request $request)
    {
        $count = AdminNotification::forUser($request->user()->id)->unread()->count();
        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, int $id)
    {
        $notification = AdminNotification::where('user_id', $request->user()->id)->find($id);
        if (!$notification) return response()->json(['error' => 'Bildirim bulunamadı'], 404);

        $notification->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        AdminNotification::forUser($request->user()->id)->unread()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
}
