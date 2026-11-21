<!DOCTYPE html>
<html lang="tr" data-theme="<?= e(auth()['theme'] ?? 'dark') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'SocialNet – Elite Social Platform') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css?v=3.0') ?>">
    <script>
        window.APP_URL = "<?= rtrim(base_url(), '/') ?>";
        window.CURRENT_USER = <?= json_encode(auth() ? ['id' => auth()['id'], 'username' => auth()['username']] : null) ?>;
    </script>
</head>
<body>
<div class="sn-layout">
    <div class="sn-layout-inner">
        <!-- 1. LEFT SIDEBAR NAVIGATION (DESKTOP) -->
        <aside class="sn-sidebar">
            <a href="<?= url('/') ?>" class="sn-brand">
                <div class="sn-brand-logo">
                    <?= icon('sparkles', 'w-5 h-5') ?>
                </div>
                <span class="sn-brand-name">SocialNet</span>
            </a>

            <ul class="sn-nav-list">
                <li>
                    <a href="<?= url('/') ?>" class="sn-nav-link <?= (($_SERVER['REQUEST_URI'] ?? '') === '/' || str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/public/') || str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/public') || str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/socialnet/')) ? 'active' : '' ?>">
                        <?= icon('home', 'w-5 h-5') ?>
                        <span>Ana Sayfa</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('/explore') ?>" class="sn-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/explore') ? 'active' : '' ?>">
                        <?= icon('explore', 'w-5 h-5') ?>
                        <span>Keşfet</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('/live') ?>" class="sn-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/live') ? 'active' : '' ?>">
                        <?= icon('video', 'w-5 h-5') ?>
                        <span>Canlı Yayın</span>
                    </a>
                </li>
                <?php if (is_logged_in()): $me = auth(); ?>
                <li>
                    <a href="<?= url('/notifications') ?>" class="sn-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/notifications') ? 'active' : '' ?>">
                        <?= icon('bell', 'w-5 h-5') ?>
                        <span>Bildirimler</span>
                        <span class="sn-nav-badge" id="sidebar-notif-badge" style="display:none;">0</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('/messages') ?>" class="sn-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/messages') ? 'active' : '' ?>">
                        <?= icon('message', 'w-5 h-5') ?>
                        <span>Mesajlar</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('/saved') ?>" class="sn-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/saved') ? 'active' : '' ?>">
                        <?= icon('bookmark', 'w-5 h-5') ?>
                        <span>Kaydedilenler</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('/u/' . $me['username']) ?>" class="sn-nav-link <?= (isset($profileUser) && $profileUser['id'] === $me['id']) ? 'active' : '' ?>">
                        <?= icon('user', 'w-5 h-5') ?>
                        <span>Profilim</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('/settings') ?>" class="sn-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/settings') ? 'active' : '' ?>">
                        <?= icon('settings', 'w-5 h-5') ?>
                        <span>Ayarlar</span>
                    </a>
                </li>
                <?php if ((int)($me['role_id'] ?? 3) === 1): ?>
                <li>
                    <a href="<?= url('/admin') ?>" class="sn-nav-link" style="color:var(--brand-accent);">
                        <?= icon('zap', 'w-5 h-5') ?>
                        <span>Yönetici Paneli</span>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="<?= url('/logout') ?>" class="sn-nav-link" style="color:var(--danger);margin-top:8px;">
                        <?= icon('logout', 'w-5 h-5') ?>
                        <span>Çıkış Yap</span>
                    </a>
                </li>
                <?php else: ?>
                <li>
                    <a href="<?= url('/login') ?>" class="sn-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/login') ? 'active' : '' ?>">
                        <?= icon('lock', 'w-5 h-5') ?>
                        <span>Giriş Yap</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('/register') ?>" class="sn-nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/register') ? 'active' : '' ?>">
                        <?= icon('sparkles', 'w-5 h-5') ?>
                        <span>Kayıt Ol</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <?php if (is_logged_in()): ?>
            <button class="sn-create-btn btn-create-post-sidebar" onclick="openCreateModal()">
                <?= icon('plus-circle', 'w-5 h-5') ?>
                <span>Gönderi Paylaş</span>
            </button>

            <!-- Bottom User Profile Card -->
            <a href="<?= url('/u/' . $me['username']) ?>" class="sn-user-card sn-sidebar-user" style="margin-top:auto;text-decoration:none;">
                <?= avatar_img($me['avatar'] ?? null, $me['full_name'] ?? $me['username'], 'sn-avatar') ?>
                <div class="sn-user-info" style="overflow:hidden;">
                    <div class="sn-user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($me['full_name'] ?: $me['username']) ?></div>
                    <div class="sn-user-handle">@<?= e($me['username']) ?></div>
                </div>
            </a>
            <?php else: ?>
            <!-- Logged Out Join Card -->
            <div class="sn-auth-cta-box" style="margin-top:auto;">
                <div style="width:36px;height:36px;border-radius:10px;background:var(--brand-gradient);display:flex;align-items:center;justify-content:center;color:#fff;margin-bottom:10px;box-shadow:var(--brand-gradient-glow);">
                    <?= icon('sparkles', 'w-4 h-4') ?>
                </div>
                <div style="font-weight:800;font-size:14px;color:var(--text-main);">Aramıza Katılın</div>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.4;margin:4px 0 12px;">Fikirlerinizi paylaşın, yeni insanlarla tanışın ve trendleri yakalayın.</p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="<?= url('/login') ?>" class="btn-primary" style="text-align:center;padding:8px;font-size:12.5px;text-decoration:none;">Giriş Yap</a>
                    <a href="<?= url('/register') ?>" class="btn-secondary" style="text-align:center;padding:7px;font-size:12.5px;text-decoration:none;">Kayıt Ol</a>
                </div>
            </div>
            <?php endif; ?>
        </aside>

        <!-- 2. CENTER CONTENT FEED -->
        <main class="sn-main sn-main-content">
            <!-- TOPBAR SEARCH -->
            <header class="sn-topbar">
                <div class="sn-search-bar sn-search-wrapper">
                    <?= icon('search', 'w-4 h-4 sn-search-icon') ?>
                    <input type="text" class="sn-search-input" id="global-search-input" placeholder="SocialNet'te ara (kullanıcı, #etiket)..." autocomplete="off">
                    <div class="sn-search-dropdown" id="global-search-dropdown"></div>
                </div>
                <div class="sn-topbar-actions">
                    <button class="sn-topbar-btn" id="theme-toggle-btn" title="Temayı Değiştir" onclick="toggleTheme()">
                        <?= icon('moon', 'w-4 h-4') ?>
                    </button>
                    <?php if (is_logged_in()): ?>
                    <a href="<?= url('/messages') ?>" class="sn-topbar-btn" title="Mesajlar">
                        <?= icon('message', 'w-4 h-4') ?>
                    </a>
                    <a href="<?= url('/notifications') ?>" class="sn-topbar-btn" title="Bildirimler" style="position:relative;">
                        <?= icon('bell', 'w-4 h-4') ?>
                        <span class="sn-badge-dot" id="topbar-notif-dot" style="display:none;"></span>
                    </a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- FLASH ALERTS -->
            <?php if ($msg = flash('success')): ?>
                <div class="sn-alert sn-alert-success"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('error')): ?>
                <div class="sn-alert sn-alert-error"><?= e($msg) ?></div>
            <?php endif; ?>

            <!-- PAGE CONTENT INJECTION -->
            <?= $content ?? '' ?>
        </main>

        <!-- 3. RIGHT WIDGET PANEL (DESKTOP) -->
        <aside class="sn-rightbar sn-right-panel">
            <!-- TRENDING WIDGET -->
            <div class="sn-widget">
                <div class="sn-widget-header">
                    <div class="sn-widget-title">
                        <span style="color:#f59e0b;"><?= icon('trending', 'w-4 h-4') ?></span>
                        <span>Trend Konular</span>
                    </div>
                </div>
                <div class="sn-widget-body" id="trending-hashtags-widget">
                    <?php
                    $trendingTags = \App\Core\Database::fetchAll("SELECT tag, posts_count FROM `hashtags` ORDER BY `posts_count` DESC LIMIT 5");
                    if (empty($trendingTags)):
                    ?>
                        <div style="font-size:13px;color:var(--text-muted);padding:8px 0;">Henüz trend etiket yok.</div>
                    <?php else: ?>
                        <?php foreach ($trendingTags as $tag): ?>
                            <a href="<?= url('/explore?q=' . urlencode('#' . $tag['tag'])) ?>" class="sn-trend-item">
                                <span class="sn-trend-tag">#<?= e($tag['tag']) ?></span>
                                <span class="sn-trend-count"><?= (int)$tag['posts_count'] ?> gönderi</span>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RECOMMENDED USERS WIDGET -->
            <div class="sn-widget">
                <div class="sn-widget-header">
                    <div class="sn-widget-title">
                        <span style="color:var(--brand-primary);"><?= icon('users', 'w-4 h-4') ?></span>
                        <span>Kimi Takip Etmeli</span>
                    </div>
                </div>
                <div class="sn-widget-body" id="suggested-users-widget">
                    <?php
                    $currentUid = \App\Core\Session::getUserId() ?? 0;
                    $suggested = \App\Core\Database::fetchAll(
                        "SELECT u.id, u.username, u.full_name, u.is_verified, p.avatar
                         FROM `users` u
                         LEFT JOIN `user_profiles` p ON p.user_id = u.id
                         WHERE u.id != :me AND u.id NOT IN (
                             SELECT following_id FROM `followers` WHERE follower_id = :me2
                         )
                         LIMIT 4",
                        ['me' => $currentUid, 'me2' => $currentUid]
                    );
                    if (empty($suggested)):
                    ?>
                        <div style="font-size:13px;color:var(--text-muted);padding:8px 0;">Tüm kullanıcıları takip ediyorsunuz.</div>
                    <?php else: ?>
                        <?php foreach ($suggested as $sug): ?>
                            <div class="sn-user-suggestion">
                                <a href="<?= url('/u/' . $sug['username']) ?>" style="display:flex;align-items:center;gap:10px;text-decoration:none;flex:1;overflow:hidden;">
                                    <?= avatar_img($sug['avatar'] ?? null, $sug['full_name'] ?? $sug['username'], 'sn-avatar', 'width:38px;height:38px;') ?>
                                    <div style="overflow:hidden;">
                                        <div style="font-size:13px;font-weight:700;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?= e($sug['full_name'] ?: $sug['username']) ?>
                                        </div>
                                        <div style="font-size:11px;color:var(--text-muted);">@<?= e($sug['username']) ?></div>
                                    </div>
                                </a>
                                <?php if (is_logged_in()): ?>
                                    <button class="btn-primary" style="padding:6px 14px;font-size:12px;" onclick="handleFollow('<?= e($sug['username']) ?>', this)">Takip Et</button>
                                <?php else: ?>
                                    <a href="<?= url('/login') ?>" class="btn-primary" style="padding:6px 14px;font-size:12px;text-decoration:none;">Takip Et</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- FOOTER INFO -->
            <div style="padding:12px 14px;font-size:11px;color:var(--text-muted);line-height:1.6;">
                <div>SocialNet Platform © <?= date('Y') ?></div>
                <div style="margin-top:4px;">Saf PHP 8.3 & WAMP & PDO MVC Mimarisi</div>
            </div>
        </aside>
    </div>
</div>

<!-- 4. MOBILE BOTTOM NAVIGATION -->
<nav class="sn-mobile-bottom-bar">
    <a href="<?= url('/') ?>" class="sn-mobile-tab <?= (($_SERVER['REQUEST_URI'] ?? '') === '/' || str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/public/')) ? 'active' : '' ?>">
        <?= icon('home', 'w-6 h-6') ?>
    </a>
    <a href="<?= url('/explore') ?>" class="sn-mobile-tab <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/explore') ? 'active' : '' ?>">
        <?= icon('explore', 'w-6 h-6') ?>
    </a>
    <?php if (is_logged_in()): ?>
    <button class="sn-mobile-tab" onclick="openCreateModal()" style="color:var(--brand-primary);background:none;border:none;cursor:pointer;">
        <?= icon('plus-circle', 'w-7 h-7') ?>
    </button>
    <a href="<?= url('/messages') ?>" class="sn-mobile-tab <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/messages') ? 'active' : '' ?>">
        <?= icon('message', 'w-6 h-6') ?>
    </a>
    <a href="<?= url('/u/' . auth()['username']) ?>" class="sn-mobile-tab <?= (isset($profileUser) && $profileUser['id'] === auth()['id']) ? 'active' : '' ?>">
        <?= avatar_img(auth()['avatar'] ?? null, auth()['full_name'] ?? auth()['username'], 'sn-avatar', 'width:26px;height:26px;') ?>
    </a>
    <?php else: ?>
    <a href="<?= url('/login') ?>" class="sn-mobile-tab">
        <?= icon('user', 'w-6 h-6') ?>
    </a>
    <?php endif; ?>
</nav>

<!-- 5. NEW POST MODAL -->
<div class="sn-modal-overlay" id="create-post-modal">
    <div class="sn-modal-card">
        <div class="sn-modal-header">
            <h3 class="sn-modal-title">Yeni Gönderi Oluştur</h3>
            <button class="sn-topbar-btn" onclick="closeCreateModal()"><?= icon('x', 'w-4 h-4') ?></button>
        </div>
        <form action="<?= url('/post/create') ?>" method="POST" enctype="multipart/form-data" style="padding:20px 24px;">
            <?= csrf_field() ?>
            <div style="display:flex;gap:12px;margin-bottom:14px;">
                <?= avatar_img(auth()['avatar'] ?? null, auth()['full_name'] ?? auth()['username'] ?? 'User', 'sn-avatar') ?>
                <div>
                    <div style="font-weight:700;font-size:14px;"><?= e(auth()['full_name'] ?? 'Misafir') ?></div>
                    <select name="visibility" class="sn-select-privacy" style="margin-top:4px;">
                        <option value="public">🌍 Herkese Açık</option>
                        <option value="followers">👥 Yalnızca Takipçiler</option>
                        <option value="private">🔒 Yalnızca Ben</option>
                    </select>
                </div>
            </div>

            <textarea name="content" class="sn-compose-textarea" placeholder="Neler düşünüyorsun? Etiket (#) ve kişi (@) ekleyebilirsin..." style="min-height:120px;" required></textarea>

            <div id="modal-preview-zone" style="display:flex;gap:10px;margin-top:12px;flex-wrap:wrap;"></div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px;padding-top:16px;border-top:1px solid var(--border-color);">
                <label class="btn-tool" style="cursor:pointer;">
                    <?= icon('image', 'w-5 h-5') ?>
                    <span>Fotoğraf / Video Ekle</span>
                    <input type="file" name="media[]" multiple accept="image/*,video/*" style="display:none;" onchange="handleModalMediaPreview(this)">
                </label>
                <button type="submit" class="btn-primary">Paylaş</button>
            </div>
        </form>
    </div>
</div>

<!-- 6. FULLSCREEN STORY VIEWER MODAL -->
<div class="sn-story-viewer-overlay" id="story-viewer-modal">
    <div class="sn-story-viewer-frame">
        <div class="sn-story-bars-row" id="story-progress-bars">
            <div class="sn-story-bar-segment"><div class="sn-story-bar-fill" style="width:100%;"></div></div>
        </div>
        <div style="position:absolute;top:22px;left:14px;right:14px;display:flex;align-items:center;justify-content:space-between;z-index:20;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div id="story-user-avatar-wrap">
                    <?= avatar_img(null, 'Kullanıcı', 'sn-avatar', 'width:32px;height:32px;', 'id="story-user-avatar"') ?>
                </div>
                <div>
                    <div style="font-weight:700;font-size:13px;color:#fff;" id="story-user-name">Kullanıcı</div>
                    <div style="font-size:11px;color:rgba(255,255,255,0.7);" id="story-user-time">Az önce</div>
                </div>
            </div>
            <button onclick="closeStoryViewer()" style="color:#fff;background:rgba(0,0,0,0.4);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border:none;cursor:pointer;">
                <?= icon('x', 'w-4 h-4') ?>
            </button>
        </div>
        <img src="" id="story-image-display" class="sn-story-media-view" alt="Hikâye">
    </div>
</div>

<!-- Toast Container -->
<div class="sn-toast-container" id="toast-container"></div>

<script src="<?= asset('js/app.js?v=3.0') ?>"></script>
</body>
</html>
