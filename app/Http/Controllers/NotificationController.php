<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
/**
 * @OA\Schema(
 *     schema="Notification",
 *     type="object",
 *     title="Notification",
 *     required={"id", "type", "data", "read_at", "created_at"},
 *     @OA\Property(property="id", type="string", example="1d9a7e91-53e1-401b-b4e7-4c5cf0c6a435"),
 *     @OA\Property(property="type", type="string", example="App\Notifications\OrderShipped"),
 *     @OA\Property(property="data", type="object", 
 *         example={"order_id": "12345", "order_status": "shipped"}
 *     ),
 *     @OA\Property(property="read_at", type="string", format="date-time", nullable=true, example="2024-09-11T12:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-11T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-11T12:10:00Z")
 * )
 */
class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
 * @OA\Get(
 *     path="/api/notifications",
 *     operationId="getNotifications",
 *     tags={"Notifications"},
 *     summary="Get user notifications",
 *     description="Fetch all notifications and unread notifications for the authenticated user.",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="notifications", type="array", 
 *                 @OA\Items(ref="#/components/schemas/Notification")
 *             ),
 *             @OA\Property(property="unread_notifications", type="array", 
 *                 @OA\Items(ref="#/components/schemas/Notification")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function getNotifications(Request $request)
    {
        $user = auth()->user();
        $notifications = $this->notificationService->getNotifications($user);

        return response()->json($notifications);
    }


    /**
 * @OA\Post(
 *     path="/api/notifications/mark-as-read",
 *     operationId="markNotificationsAsRead",
 *     tags={"Notifications"},
 *     summary="Mark all notifications as read",
 *     description="Marks all unread notifications for the authenticated user as read.",
 *     @OA\Response(
 *         response=200,
 *         description="Notifications marked as read",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Notifications marked as read")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function markAsRead()
    {
        $user = auth()->user();
        $response = $this->notificationService->markAllNotificationsAsRead($user);

        return response()->json($response);
    }

}
