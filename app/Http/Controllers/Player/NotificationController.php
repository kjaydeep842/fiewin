<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($request->expectsJson() || $request->ajax()) {
            $notifications = Notification::where('user_id', $user->id)
                ->latest()
                ->take(30)
                ->get()
                ->map(function ($notif) {
                    return [
                        'id' => $notif->id,
                        'title' => $notif->title,
                        'message' => $notif->message,
                        'type' => $notif->type ?? 'general',
                        'is_read' => (bool)$notif->is_read,
                        'created_at_human' => $notif->created_at->diffForHumans(),
                    ];
                });

            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
                'notifications' => $notifications,
            ]);
        }

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('player.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function unreadCount(Request $request)
    {
        $unreadCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'Unauthorized');
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            $unreadCount = Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount,
            ]);
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function realtimeCheck(Request $request)
    {
        $lastChecked = $request->input('last_checked_at');
        $since = $lastChecked ? \Carbon\Carbon::parse($lastChecked) : now()->subSeconds(15);
        $currentTime = now()->toIso8601String();

        $newNotifs = Notification::where('user_id', auth()->id())
            ->where('created_at', '>', $since)
            ->latest()
            ->get();

        $unreadCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'server_time' => $currentTime,
            'new_notifications' => $newNotifs,
            'unread_count' => $unreadCount,
        ]);
    }
}
