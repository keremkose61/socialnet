<?php

use App\Core\Session;
use App\Models\User;

if (!function_exists('base_url')) {
    function base_url(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);

        if ($baseDir === '/' || $baseDir === '\\' || $baseDir === '.' || empty($baseDir)) {
            return $protocol . $host;
        }

        return $protocol . $host . str_replace('\\', '/', $baseDir);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(base_url(), '/');
        $path = ltrim($path, '/');
        return $path === '' ? $base : $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('upload')) {
    function upload(?string $path, string $default = ''): string
    {
        if (empty($path)) {
            return $default ? upload($default) : '';
        }
        $normalized = ltrim($path, '/');
        if ($normalized === 'default-avatar.png' || $normalized === 'default-avatar.svg') {
            return asset('images/default-avatar.svg');
        }
        if ($normalized === 'default-cover.jpg') {
            return url('uploads/covers/default-cover.jpg?v=4');
        }
        return url('uploads/' . $normalized);
    }
}

if (!function_exists('avatar_fallback')) {
    function avatar_fallback(string $name = 'User'): string
    {
        $name = trim($name);
        $char = mb_strtoupper(mb_substr($name !== '' ? $name : 'S', 0, 1, 'UTF-8'), 'UTF-8');
        
        // Dynamic vibrant color pairs based on name
        $gradients = [
            ['#8B5CF6', '#22D3EE'],
            ['#EC4899', '#8B5CF6'],
            ['#3B82F6', '#10B981'],
            ['#F59E0B', '#EF4444'],
            ['#6366F1', '#A855F7'],
            ['#06B6D4', '#3B82F6']
        ];
        $idx = abs(crc32($name)) % count($gradients);
        $c1 = $gradients[$idx][0];
        $c2 = $gradients[$idx][1];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100">'
            . '<defs>'
            . '<linearGradient id="avG" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="' . $c1 . '"/><stop offset="100%" stop-color="' . $c2 . '"/></linearGradient>'
            . '<radialGradient id="avR" cx="30%" cy="30%" r="70%"><stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.25"/><stop offset="100%" stop-color="#000000" stop-opacity="0.4"/></radialGradient>'
            . '</defs>'
            . '<rect width="100" height="100" rx="50" fill="#09090F"/>'
            . '<circle cx="50" cy="50" r="47" fill="url(#avG)"/>'
            . '<circle cx="50" cy="50" r="47" fill="url(#avR)"/>'
            . '<circle cx="50" cy="50" r="43" fill="#12121C" opacity="0.3"/>'
            . '<text x="50" y="55" font-family="-apple-system,BlinkMacSystemFont,\'Inter\',\'Segoe UI\',sans-serif" font-size="38" font-weight="900" fill="#FFFFFF" text-anchor="middle" dominant-baseline="central" style="letter-spacing:-0.03em;">' . htmlspecialchars($char, ENT_QUOTES, 'UTF-8') . '</text>'
            . '</svg>';
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}

if (!function_exists('avatar_img')) {
    function avatar_img(?string $path, string $name = 'User', string $class = 'sn-avatar', string $style = '', string $extra = ''): string
    {
        $fallback = avatar_fallback($name);
        $src = (empty($path) || $path === 'default-avatar.png' || $path === 'default-avatar.svg')
            ? $fallback
            : upload($path);
        
        $alt = htmlspecialchars($name ?: 'Kullanıcı', ENT_QUOTES, 'UTF-8');
        $cls = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
        $stl = $style ? ' style="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '"' : '';

        return sprintf(
            '<img src="%s" alt="%s" class="%s"%s onerror="this.onerror=null;this.src=\'%s\';" %s>',
            htmlspecialchars($src, ENT_QUOTES, 'UTF-8'),
            $alt,
            $cls,
            $stl,
            $fallback,
            $extra
        );
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        $userId = Session::getUserId();
        if (!$userId) {
            return null;
        }
        static $cachedUser = null;
        if ($cachedUser === null || ($cachedUser['id'] ?? null) !== $userId) {
            $userModel = new User();
            $cachedUser = $userModel->findWithProfile($userId);
        }
        return $cachedUser;
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return Session::isLoggedIn();
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $type): ?string
    {
        return Session::getFlash($type);
    }
}

if (!function_exists('format_time_ago')) {
    function format_time_ago(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Az önce';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return "{$mins} dk önce";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "{$hours} sa önce";
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "{$days} gün önce";
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "{$weeks} hf önce";
        } else {
            return date('d.m.Y', $timestamp);
        }
    }
}

if (!function_exists('dd')) {
    function dd(...$vars): void
    {
        echo '<pre style="background:#111;color:#00ff66;padding:20px;font-size:14px;border-radius:8px;overflow:auto;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        exit;
    }
}

/**
 * Modern SVG Icon Helper
 * Returns clean, vector-sharp Lucide-style SVG markup
 */
if (!function_exists('icon')) {
    function icon(string $name, string $class = 'w-5 h-5'): string
    {
        $icons = [
            'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'explore' => '<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>',
            'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
            'message' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
            'bookmark' => '<path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>',
            'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
            'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
            'plus-circle' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
            'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
            'share' => '<path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/>',
            'chat' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
            'trash' => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
            'image' => '<rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>',
            'video' => '<path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/>',
            'globe' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
            'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'check' => '<polyline points="20 6 9 17 4 12"/>',
            'badge-check' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
            'trending' => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',
            'sparkles' => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>',
            'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>',
            'moon' => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
            'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
            'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'more' => '<circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/>',
            'camera' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
            'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>',
            'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
            'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
            'map-pin' => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
            'calendar' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
            'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
            'smile' => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
        ];

        $body = $icons[$name] ?? $icons['sparkles'];
        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" class="sn-icon %s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%s</svg>',
            htmlspecialchars($class),
            $body
        );
    }
}
