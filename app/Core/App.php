<?php

namespace App\Core;

use Throwable;

class App
{
    private static ?App $instance = null;
    private array $config = [];

    public function __construct(string $basePath)
    {
        self::$instance = $this;

        // 1. Register PSR-4 autoloader
        require_once $basePath . '/app/Core/Autoloader.php';
        Autoloader::register();

        // 2. Load Helpers
        require_once $basePath . '/app/Helpers/functions.php';

        // 3. Load Environment Variables
        Env::load($basePath . '/.env');

        // 4. Load App Configuration
        $this->config = require $basePath . '/config/app.php';

        // 5. Configure PHP runtime
        date_default_timezone_set($this->config['timezone'] ?? 'Europe/Istanbul');
        if ($this->config['debug']) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            error_reporting(0);
        }

        // 6. Start Secure Session
        Session::start();
    }

    public static function getInstance(): ?App
    {
        return self::$instance;
    }

    public function getConfig(string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }
        return $this->config[$key] ?? $default;
    }

    public function run(): void
    {
        $request = new Request();
        $response = new Response();

        try {
            // Load routes
            $routesFile = dirname(__DIR__, 2) . '/routes/web.php';
            if (file_exists($routesFile)) {
                require_once $routesFile;
            }

            // Dispatch Request
            Router::dispatch($request, $response);

        } catch (Throwable $e) {
            $this->handleException($e, $request, $response);
        }
    }

    private function handleException(Throwable $e, Request $request, Response $response): void
    {
        // Log the exception
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/app_' . date('Y-m-d') . '.log';
        $logMessage = sprintf(
            "[%s] %s in %s on line %d\nStack Trace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        $response->setStatusCode(500);

        if ($request->isAjax()) {
            $response->json([
                'success' => false,
                'message' => $this->config['debug'] ? $e->getMessage() : 'Sunucuda bir hata oluştu.',
                'debug'   => $this->config['debug'] ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString())
                ] : null
            ], 500);
        } else {
            $debug = $this->config['debug'];
            echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Hata - SocialNet</title>";
            echo "<style>body{background:#0f172a;color:#f8fafc;font-family:system-ui,sans-serif;padding:40px;line-height:1.6;}";
            echo ".box{max-width:800px;margin:0 auto;background:#1e293b;padding:30px;border-radius:12px;border:1px solid #334155;}";
            echo "h1{color:#f43f5e;font-size:24px;margin-top:0;} pre{background:#090d16;padding:15px;border-radius:8px;overflow-x:auto;color:#38bdf8;font-size:13px;}</style></head>";
            echo "<body><div class='box'>";
            echo "<h1>⚠️ Bir Sistem Hatası Oluştu</h1>";
            if ($debug) {
                echo "<p><strong>Mesaj:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
                echo "<p><strong>Dosya:</strong> " . htmlspecialchars($e->getFile()) . " (Satır " . $e->getLine() . ")</p>";
                echo "<h3>Hata Detayı:</h3><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            } else {
                echo "<p>Beklenmeyen bir hata meydana geldi. Sistem yöneticileri durumdan haberdar edildi.</p>";
                echo "<p><a href='" . url('/') . "' style='color:#38bdf8;'>Ana Sayfaya Dön</a></p>";
            }
            echo "</div></body></html>";
        }
        exit;
    }
}
