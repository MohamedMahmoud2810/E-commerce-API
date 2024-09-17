<?php

namespace App\Services;

use App\Repositories\NotificationRepository;

class NotificationService
{
    protected $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getNotifications($user)
    {
        return [
            'notifications' => $this->notificationRepository->getUserNotifications($user),
            'unread_notifications' => $this->notificationRepository->getUnreadNotifications($user),
        ];
    }

    public function markAllNotificationsAsRead($user)
    {
        $this->notificationRepository->markAllAsRead($user);
        return ['message' => 'Notifications marked as read'];
    }
}
