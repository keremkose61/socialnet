<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    private Notification $notifModel;

    public function __construct()
    {
        $this->notifModel = new Notification();
    }

    public function index(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $notifications = $this->notifModel->getUserNotifications($user['id'], 50);
        $this->notifModel->markAllAsRead($user['id']);

        $this->render('notifications/index', [
            'pageTitle'     => 'Bildirimler - SocialNet',
            'currentUser'   => $user,
            'notifications' => $notifications
        ]);
    }

    public function unreadCount(): void
    {
        if (!is_logged_in()) {
            $this->json(['count' => 0]);
            return;
        }

        $user = $this->currentUser();
        $count = $this->notifModel->getUnreadCount($user['id']);
        $this->json(['count' => $count]);
    }
}
