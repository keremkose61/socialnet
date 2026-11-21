<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Yönetim Paneli - SocialNet') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body style="background:#090d16;">
<div class="app-container">
    <aside class="app-sidebar" style="background:#0f172a;">
        <a href="<?= url('/admin') ?>" class="brand-logo">
            <div class="brand-icon" style="background:linear-gradient(135deg, #06b6d4, #3b82f6);">⚡</div>
            <span class="brand-title">AdminNet</span>
        </a>

        <ul class="nav-menu">
            <li class="nav-item active">
                <a href="<?= url('/admin') ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Genel Bakış</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= url('/') ?>">
                    <span class="nav-icon">🌐</span>
                    <span class="nav-text">Siteye Dön</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= url('/logout') ?>" style="color:#ef4444;">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Çıkış Yap</span>
                </a>
            </li>
        </ul>

        <div style="padding:12px;background:rgba(255,255,255,0.03);border-radius:var(--radius-md);margin-top:auto;font-size:12px;color:var(--text-muted);">
            Yönetici: <strong>@<?= e(auth()['username']) ?></strong>
        </div>
    </aside>

    <main class="app-main" style="padding:32px;">
        <div style="width:100%;max-width:1100px;">
            <?= $content ?>
        </div>
    </main>
</div>
</body>
</html>
