<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notification types
     */
    const TYPE_ORDER_UPDATE = 'order_update';
    const TYPE_SPECIAL_OFFER = 'special_offer';
    const TYPE_NEW_PRODUCT = 'new_product';
    const TYPE_GENERAL = 'general';

    /**
     * Create a notification
     */
    public static function create(
        User $user,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $icon = null,
        string $color = 'violet'
    ): ?Notification {
        try {
            return Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'action_url' => $actionUrl,
                'icon' => $icon,
                'color' => $color,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send order status update notification
     */
    public static function orderStatusUpdated(Order $order, string $oldStatus): ?Notification
    {
        $statusMessages = [
            'confirmed' => [
                'title' => 'Order Confirmed! 🎉',
                'message' => "Your order #{$order->id} has been confirmed. We're preparing your items!",
                'color' => 'green',
            ],
            'processing' => [
                'title' => 'Order Processing ⚙️',
                'message' => "Your order #{$order->id} is being processed and will be shipped soon.",
                'color' => 'blue',
            ],
            'shipped' => [
                'title' => 'Order Shipped! 🚚',
                'message' => "Great news! Your order #{$order->id} has been shipped and is on its way.",
                'color' => 'purple',
            ],
            'delivered' => [
                'title' => 'Order Delivered! 🎁',
                'message' => "Your order #{$order->id} has been delivered. Enjoy your Valorant collectibles!",
                'color' => 'green',
            ],
            'cancelled' => [
                'title' => 'Order Cancelled',
                'message' => "Your order #{$order->id} has been cancelled. Contact us if you have questions.",
                'color' => 'red',
            ],
        ];

        $notification = $statusMessages[$order->status] ?? [
            'title' => 'Order Update',
            'message' => "Your order #{$order->id} status has been updated to " . ucfirst($order->status),
            'color' => 'violet',
        ];

        return self::create(
            $order->user,
            self::TYPE_ORDER_UPDATE,
            $notification['title'],
            $notification['message'],
            [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $order->status,
            ],
            route('profile.orders.show', $order),
            '📦',
            $notification['color']
        );
    }

    /**
     * Send new order notification
     */
    public static function orderPlaced(Order $order): ?Notification
    {
        return self::create(
            $order->user,
            self::TYPE_ORDER_UPDATE,
            'Order Placed Successfully! 🎉',
            "Thank you for your order! Order #{$order->id} has been received and is being processed.",
            ['order_id' => $order->id],
            route('profile.orders.show', $order),
            '✅',
            'green'
        );
    }

    /**
     * Send new product notification to all users
     */
    public static function newProduct(Product $product): void
    {
        try {
            $users = User::all();
            
            foreach ($users as $user) {
                self::create(
                    $user,
                    self::TYPE_NEW_PRODUCT,
                    'New Product Available! ✨',
                    "Check out our new product: {$product->name}",
                    ['product_id' => $product->id],
                    route('shop.show', $product),
                    '🆕',
                    'purple'
                );
            }

            Log::info('New product notifications sent', [
                'product_id' => $product->id,
                'user_count' => $users->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send new product notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send special offer notification to all users
     */
    public static function specialOffer(string $title, string $message, ?string $url = null): void
    {
        try {
            $users = User::all();
            
            foreach ($users as $user) {
                self::create(
                    $user,
                    self::TYPE_SPECIAL_OFFER,
                    $title,
                    $message,
                    null,
                    $url ?? route('shop.index'),
                    '🎁',
                    'yellow'
                );
            }

            Log::info('Special offer notifications sent', [
                'user_count' => $users->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send special offer notifications', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send custom notification to specific user
     */
    public static function sendCustom(
        User $user,
        string $title,
        string $message,
        ?string $url = null,
        ?string $icon = null
    ): ?Notification {
        return self::create(
            $user,
            self::TYPE_GENERAL,
            $title,
            $message,
            null,
            $url,
            $icon ?? '📢',
            'violet'
        );
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead(int $notificationId, User $user): bool
    {
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if ($notification) {
                $notification->markAsRead();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public static function markAllAsRead(User $user): int
    {
        try {
            return Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Delete old read notifications
     */
    public static function cleanupOldNotifications(int $days = 30): int
    {
        try {
            return Notification::where('is_read', true)
                ->where('read_at', '<', now()->subDays($days))
                ->delete();
        } catch (\Exception $e) {
            Log::error('Failed to cleanup old notifications', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Get unread count for user
     */
    public static function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
}










