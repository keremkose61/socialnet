<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\User;

class AdminController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        if ((int)$user['role_id'] !== 1) {
            Session::setFlash('error', 'Yönetici yetkisi gereklidir.');
            $this->redirect(url('/'));
            return;
        }

        $totalUsers = Database::fetch("SELECT COUNT(*) as c FROM `users`")['c'] ?? 0;
        $totalPosts = Database::fetch("SELECT COUNT(*) as c FROM `posts`")['c'] ?? 0;
        $totalComments = Database::fetch("SELECT COUNT(*) as c FROM `comments`")['c'] ?? 0;
        $totalReports = Database::fetch("SELECT COUNT(*) as c FROM `reports` WHERE `status` = 'pending'")['c'] ?? 0;

        $recentUsers = Database::fetchAll("SELECT u.*, prof.avatar FROM `users` u LEFT JOIN `user_profiles` prof ON prof.user_id = u.id ORDER BY u.created_at DESC LIMIT 10");
        $recentReports = Database::fetchAll("SELECT r.*, u.username as reporter_username FROM `reports` r JOIN `users` u ON u.id = r.reporter_id ORDER BY r.created_at DESC LIMIT 10");

        $this->render('admin/index', [
            'pageTitle'     => 'Yönetim Paneli - SocialNet',
            'currentUser'   => $user,
            'totalUsers'    => (int)$totalUsers,
            'totalPosts'    => (int)$totalPosts,
            'totalComments' => (int)$totalComments,
            'totalReports'  => (int)$totalReports,
            'recentUsers'   => $recentUsers,
            'recentReports' => $recentReports
        ], layout: 'layouts/admin');
    }

    public function toggleBan(int $userId): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        if ((int)$user['role_id'] !== 1) {
            $this->json(['success' => false, 'message' => 'Yetkisiz erişim.'], 403);
            return;
        }

        $target = (new User())->find($userId);
        if (!$target || (int)$target['id'] === $user['id']) {
            $this->json(['success' => false, 'message' => 'Kendi hesabınızı yasaklayamazsınız.'], 400);
            return;
        }

        $newBan = (int)$target['is_banned'] === 1 ? 0 : 1;
        Database::execute("UPDATE `users` SET `is_banned` = :b WHERE `id` = :id", ['b' => $newBan, 'id' => $userId]);

        $this->json(['success' => true, 'is_banned' => $newBan === 1]);
    }
}
