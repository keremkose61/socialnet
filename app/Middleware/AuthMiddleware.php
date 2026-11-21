<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    public function handle(Request $request, Response $response): void
    {
        if (!Session::isLoggedIn()) {
            if ($request->isAjax()) {
                $response->json(['success' => false, 'message' => 'Lütfen önce giriş yapınız.'], 401);
            } else {
                Session::setFlash('error', 'Bu sayfayı görüntülemek için giriş yapmanız gerekiyor.');
                $response->redirect(url('/login'));
            }
        }
    }
}
