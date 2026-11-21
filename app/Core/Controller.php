<?php

namespace App\Core;

use App\Models\User;
use Exception;

abstract class Controller
{
    protected ?Request $request = null;
    protected ?Response $response = null;
    protected ?array $currentUser = null;

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewsPath = dirname(__DIR__, 2) . '/views/';
        $viewFile = $viewsPath . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }

        // Extract variables to view scope
        extract($data);

        // Start output buffering for the view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // If layout is disabled (empty string or false)
        if (!$layout) {
            echo $content;
            return;
        }

        $layoutFile = $viewsPath . str_replace('.', '/', $layout) . '.php';
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found: {$layoutFile}");
        }

        // Layout has access to $content and all $data
        require $layoutFile;
    }

    protected function json(array $data, int $status = 200): void
    {
        $this->response->json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): void
    {
        $this->response->redirect($url, $status);
    }

    protected function validate(array $rules): array
    {
        return $this->request->validate($rules);
    }

    protected function currentUser(): ?array
    {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }

        $userId = Session::getUserId();
        if (!$userId) {
            return null;
        }

        $userModel = new User();
        $this->currentUser = $userModel->findWithProfile($userId);
        return $this->currentUser;
    }

    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'Lütfen önce giriş yapınız.'], 401);
            } else {
                Session::setFlash('error', 'Bu sayfayı görüntülemek için giriş yapmalısınız.');
                $this->redirect(url('/login'));
            }
        }
    }
}
