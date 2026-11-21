<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $getParams;
    private array $postParams;
    private array $files;
    private array $headers;
    private ?array $jsonBody = null;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->getParams = $_GET;
        $this->postParams = $_POST;
        $this->files = $_FILES;
        $this->headers = $this->getAllHeaders();
        $this->uri = $this->parseUri();

        // Parse JSON payload if Content-Type is application/json
        $contentType = $this->getHeader('content-type', '');
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $this->jsonBody = $decoded;
            }
        }
    }

    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?'); // strip query string

        // If 'url' query param is passed via htaccess rewrite
        if (!empty($_GET['url'])) {
            $uri = '/' . ltrim($_GET['url'], '/');
            return $uri;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = str_replace('\\', '/', dirname($scriptName)); // e.g. /socialnet/public

        if ($baseDir !== '/' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir));
        } else {
            // Also check parent directory if accessing without /public (e.g. /socialnet)
            $parentDir = str_replace('\\', '/', dirname($baseDir));
            if ($parentDir !== '/' && !empty($parentDir) && str_starts_with($uri, $parentDir)) {
                $uri = substr($uri, strlen($parentDir));
                // If it starts with /public, strip that too
                if (str_starts_with($uri, '/public')) {
                    $uri = substr($uri, 7);
                }
            }
        }

        $uri = '/' . trim($uri, '/');
        return $uri === '//' ? '/' : $uri;
    }

    public function getMethod(): string
    {
        // Support method spoofing for PUT/DELETE via _method post param
        if ($this->method === 'POST' && !empty($this->postParams['_method'])) {
            return strtoupper($this->postParams['_method']);
        }
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            str_contains($this->getHeader('accept', ''), 'application/json');
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if ($this->jsonBody !== null && array_key_exists($key, $this->jsonBody)) {
            return $this->jsonBody[$key];
        }
        if (array_key_exists($key, $this->postParams)) {
            return $this->postParams[$key];
        }
        if (array_key_exists($key, $this->getParams)) {
            return $this->getParams[$key];
        }
        return $default;
    }

    public function all(): array
    {
        $all = array_merge($this->getParams, $this->postParams);
        if ($this->jsonBody !== null) {
            $all = array_merge($all, $this->jsonBody);
        }
        return $all;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    public function getHeader(string $name, mixed $default = null): mixed
    {
        $normalized = strtolower($name);
        return $this->headers[$normalized] ?? $default;
    }

    private function getAllHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$headerName] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerName = strtolower(str_replace('_', '-', $key));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    public function validate(array $rules): array
    {
        $errors = [];
        $data = $this->all();

        foreach ($rules as $field => $ruleString) {
            $ruleList = explode('|', $ruleString);
            $val = $data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $param = null;
                if (str_contains($rule, ':')) {
                    list($rule, $param) = explode(':', $rule, 2);
                }

                switch ($rule) {
                    case 'required':
                        if ($val === null || (is_string($val) && trim($val) === '') || (is_array($val) && empty($val))) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " alanı zorunludur.";
                        }
                        break;
                    case 'email':
                        if (!empty($val) && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "Geçerli bir e-posta adresi giriniz.";
                        }
                        break;
                    case 'min':
                        if (!empty($val) && strlen((string)$val) < (int)$param) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " en az {$param} karakter olmalıdır.";
                        }
                        break;
                    case 'max':
                        if (!empty($val) && strlen((string)$val) > (int)$param) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " en fazla {$param} karakter olabilir.";
                        }
                        break;
                    case 'alpha_num':
                        if (!empty($val) && !preg_match('/^[a-zA-Z0-9_]+$/', (string)$val)) {
                            $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . " yalnızca harf, rakam ve alt çizgi içerebilir.";
                        }
                        break;
                    case 'matches':
                        if ($val !== ($data[$param] ?? null)) {
                            $errors[$field][] = "Şifreler birbiriyle eşleşmiyor.";
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}
