<?php

return [
    'name'     => $_ENV['APP_NAME'] ?? 'SocialNet',
    'env'      => $_ENV['APP_ENV'] ?? 'development',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'url'      => rtrim($_ENV['APP_URL'] ?? 'http://localhost/socialnet/public', '/'),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Europe/Istanbul',
    'root_path' => dirname(__DIR__),
    'storage_path' => dirname(__DIR__) . '/storage',
    'uploads_path' => dirname(__DIR__) . '/public/uploads',
    'upload_max_size' => (int)($_ENV['UPLOAD_MAX_FILESIZE'] ?? 20971520),
];
