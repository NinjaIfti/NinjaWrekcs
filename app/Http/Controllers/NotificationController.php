<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display notifications center
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $filter = $request->query('filter', 'all'); // all, unread, order_updates, offers
        
        $query = $user->notifications();
        
        // Apply filters
        switch ($filter) {
            case 'unread':
                $query->unread();
                break;
            case 'order_updates':
                $query->ofType(NotificationService::TYPE_ORDER_UPDATE);
                break;
            case 'offers':
                $query->ofType(NotificationService::TYPE_SPECIAL_OFFER);
                break;
            case 'products':
                $query->ofType(NotificationService::TYPE_NEW_PRODUCT);
                break;
        }
        
        $notifications = $query->paginate(20);
        $unreadCount = NotificationService::getUnreadCount($user);
        
        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $success = NotificationService::markAsRead($id, $request->user());
        
        if ($success) {
            return response()->json([
                'success' => true,
                'unread_count' => NotificationService::getUnreadCount($request->user()),
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Notification not found',
        ], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $count = NotificationService::markAllAsRead($request->user());
        
        return redirect()->back()->with('success', "Marked {$count} notifications as read.");
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->delete();
            return redirect()->back()->with('success', 'Notification deleted.');
        }
        
        return redirect()->back()->with('error', 'Notification not found.');
    }

    /**
     * Get unread count (for AJAX)
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => NotificationService::getUnreadCount($request->user()),
        ]);
    }
}
