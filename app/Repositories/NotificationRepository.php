<?php

namespace App\Repositories;

use App\Models\User;

class NotificationRepository
{
    public function getUserNotifications($user)
    {
        return $user->notifications;
    }

    public function getUnreadNotifications($user)
    {
        return $user->unreadNotifications;
    }

    public function markAllAsRead($user)
    {
        $user->unreadNotifications->markAsRead();
    }
}
