<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     * Paginated notifications with branch-wise filtering
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->branch_id;
        $userId = $user->id;

        // Branch logic based on role
        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = $userBranchId;
        } elseif ($role === 'admin' && !empty($request->selectedSubAdminId)) {
            $branchIdToUse = $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        // Build query with branch filtering
        $query = Notification::where('branch_id', $branchIdToUse)
            ->orderBy('created_at', 'desc');

        // Add type filter if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // For staff, also filter by user_id
        if ($role === 'staff') {
            $query->where('user_id', $userId);
        }

        // Get paginated results
        $notifications = $query->paginate($perPage);

        // Format each notification for the frontend
        $formattedData = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'is_read' => (bool) $notification->is_read,
                'link' => $notification->link ?? '#',
                'created_at' => $notification->created_at->toISOString(),
                'formatted_date' => $this->formatDate($notification->created_at),
                'formatted_time' => $notification->created_at->format('h:i A'),
            ];
        });

        // Calculate unread count for the same branch
        $unreadCountQuery = Notification::where('branch_id', $branchIdToUse)
            ->where('is_read', false);

        if ($role === 'staff') {
            $unreadCountQuery->where('user_id', $userId);
        }

        $unreadCount = $unreadCountQuery->count();

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully',
            'data' => $formattedData,
            'count' => $unreadCount, // This is the unread count
            'total_unread' => $unreadCount, // Add this for clarity
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ]
        ]);
    }

    /**
     * POST /api/notifications/{id}/read
     * Mark a single notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = Auth::guard('api')->user();
            $userId = $user->id;

            // Find notification by ID
            $notification = Notification::find($id);

            if (!$notification) {
                return response()->json([
                    'status' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            // Check if this notification belongs to the user
            if ($notification->user_id != $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to mark this notification as read'
                ], 403);
            }

            $notification->is_read = true;
            $notification->save();

            // Get updated unread count
            $unreadCount = Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'status' => true,
                'message' => 'Notification marked as read',
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error marking notification as read'
            ], 500);
        }
    }

    /**
     * POST /api/notifications/read-all
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::guard('api')->user();
            $userId = $user->id;

            // Mark all unread notifications for this user as read
            $updatedCount = Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'status' => true,
                'message' => $updatedCount . ' notifications marked as read',
                'unread_count' => 0 // After marking all, unread count becomes 0
            ]);

        } catch (\Exception $e) {
            Log::error('Mark all as read error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error marking notifications as read'
            ], 500);
        }
    }

    /**
     * DELETE /api/notifications/{id}/delete
     * Delete a single notification
     */
    public function delete($id)
    {
        try {
            $user = Auth::guard('api')->user();
            $userId = $user->id;

            // Find notification by ID
            $notification = Notification::find($id);

            if (!$notification) {
                return response()->json([
                    'status' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            // Check if this notification belongs to the user
            // if ($notification->user_id != $userId) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'You do not have permission to delete this notification'
            //     ], 403);
            // }

            // Delete the notification
            $notification->delete();

            // Get updated unread count
            $unreadCount = Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'status' => true,
                'message' => 'Notification deleted successfully',
                'unread_count' => $unreadCount
            ]);

        } catch (\Exception $e) {
            Log::error('Delete notification error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error deleting notification'
            ], 500);
        }
    }

    /**
     * DELETE /api/notifications/delete-all
     * Delete all notifications
     */
    public function deleteAll()
    {
        try {
            $user = Auth::guard('api')->user();
            $userId = $user->id;

            // Delete all notifications for this user
            $deletedCount = Notification::where('user_id', $userId)->delete();

            return response()->json([
                'status' => true,
                'message' => $deletedCount . ' notifications deleted successfully',
                'unread_count' => 0 // After deleting all, unread count becomes 0
            ]);

        } catch (\Exception $e) {
            Log::error('Delete all notifications error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error deleting notifications'
            ], 500);
        }
    }

    /**
     * Helper function to format date
     */
    private function formatDate($date)
    {
        if ($date->isToday()) {
            return 'Today';
        } elseif ($date->isYesterday()) {
            return 'Yesterday';
        } else {
            return $date->format('d M Y');
        }
    }
}
