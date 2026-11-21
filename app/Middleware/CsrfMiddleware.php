<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    public function handle(Request $request, Response $response): void
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $token = $request->input('_csrf_token') ?? $request->getHeader('x-csrf-token');

            if (!Session::validateCsrf($token)) {
                if ($request->isAjax()) {
                    $response->json(['success' => false, 'message' => 'Oturum güvenlik anahtarı (CSRF) geçersiz veya süresi doldu.'], 419);
                } else {
                    Session::setFlash('error', 'İstek güvenlik doğrulamasından geçemedi. Lütfen sayfayı yenileyip tekrar deneyin.');
                    $response->redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
                }
            }
        }
    }
}
