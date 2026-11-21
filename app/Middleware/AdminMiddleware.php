<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

class AdminMiddleware
{
    public function handle(Request $request, Response $response): void
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Yönetici paneline erişmek için giriş yapmalısınız.');
            $response->redirect(url('/login'));
            return;
        }

        $userId = Session::getUserId();
        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user || (int)$user['role_id'] !== 1) {
            if ($request->isAjax()) {
                $response->json(['success' => false, 'message' => 'Bu işlem için yönetici yetkisi gereklidir.'], 403);
            } else {
                Session::setFlash('error', 'Bu sayfaya erişim yetkiniz bulunmuyor.');
                $response->redirect(url('/'));
            }
        }
    }
}
