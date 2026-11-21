<!-- 1. CATEGORY FILTER CHIPS -->
<div class="sn-explore-categories">
    <a href="<?= url('/explore') ?>" class="sn-category-chip <?= empty($query) ? 'active' : '' ?>">
        <span>🔥</span>
        <span>Trendler</span>
    </a>
    <a href="<?= url('/explore?q=%23socialnet') ?>" class="sn-category-chip <?= ($query === '#socialnet') ? 'active' : '' ?>">
        <span>⚡</span>
        <span>#socialnet</span>
    </a>
    <a href="<?= url('/explore?q=%23teknoloji') ?>" class="sn-category-chip <?= ($query === '#teknoloji') ? 'active' : '' ?>">
        <span>💻</span>
        <span>Teknoloji</span>
    </a>
    <a href="<?= url('/explore?q=%23tasarim') ?>" class="sn-category-chip <?= ($query === '#tasarim') ? 'active' : '' ?>">
        <span>🎨</span>
        <span>Tasarım & UI/UX</span>
    </a>
    <a href="<?= url('/explore?q=%23yazilim') ?>" class="sn-category-chip <?= ($query === '#yazilim') ? 'active' : '' ?>">
        <span>⚡</span>
        <span>Yazılım & PHP</span>
    </a>
    <a href="<?= url('/explore?q=%23fotograf') ?>" class="sn-category-chip <?= ($query === '#fotograf') ? 'active' : '' ?>">
        <span>📸</span>
        <span>Fotoğrafçılık</span>
    </a>
    <a href="<?= url('/explore?q=%23muzik') ?>" class="sn-category-chip <?= ($query === '#muzik') ? 'active' : '' ?>">
        <span>🎵</span>
        <span>Müzik & Lo-Fi</span>
    </a>
    <a href="<?= url('/explore?q=%23ai') ?>" class="sn-category-chip <?= ($query === '#ai') ? 'active' : '' ?>">
        <span>🧠</span>
        <span>Yapay Zekâ</span>
    </a>
</div>

<?php if (!empty($query)): ?>
    <!-- SEARCH RESULTS HEADER -->
    <div class="sn-explore-header-banner">
        <div>
            <div class="sn-explore-breadcrumb">
                <a href="<?= url('/explore') ?>">Keşfet</a>
                <span>/</span>
                <span>Arama</span>
            </div>
            <h1 class="sn-explore-title">
                "<?= e($query) ?>" Arama Sonuçları
            </h1>
            <p class="sn-explore-subtitle">Eşleşen kişiler, etiketler ve görsel keşif gönderileri</p>
        </div>
        <a href="<?= url('/explore') ?>" class="btn-secondary" style="font-size:12.5px;padding:8px 16px;">Tüm Keşfet'e Dön</a>
    </div>

    <!-- MATCHED USERS -->
    <?php if (!empty($searchResults['users'])): ?>
    <div style="padding:16px 24px;border-bottom:1px solid var(--border-color);">
        <div style="font-size:11.5px;font-weight:800;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;">Eşleşen İçerik Üreticileri</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:12px;">
            <?php foreach ($searchResults['users'] as $usr): ?>
            <div class="sn-widget" style="padding:12px 14px;display:flex;align-items:center;justify-content:space-between;margin:0;">
                <a href="<?= url('/u/' . $usr['username']) ?>" style="display:flex;align-items:center;gap:12px;min-width:0;text-decoration:none;">
                    <?= avatar_img($usr['avatar'] ?? null, $usr['full_name'] ?? $usr['username'], 'sn-avatar', 'width:42px;height:42px;') ?>
                    <div style="min-width:0;">
                        <div style="font-weight:700;font-size:13.5px;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= e($usr['full_name']) ?>
                            <?php if (!empty($usr['is_verified'])): ?>
                                <span class="badge-verified" title="Doğrulanmış"><?= icon('badge-check', 'w-3.5 h-3.5') ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:11.5px;color:var(--text-muted);">@<?= e($usr['username']) ?></div>
                    </div>
                </a>
                <?php if (is_logged_in() && auth()['id'] !== (int)$usr['id']): ?>
                    <button class="btn-primary" style="padding:5px 12px;font-size:11.5px;" onclick="handleFollow('<?= e($usr['username']) ?>', this)">Takip Et</button>
                <?php else: ?>
                    <a href="<?= url('/u/' . $usr['username']) ?>" class="btn-secondary" style="padding:5px 12px;font-size:11.5px;text-decoration:none;">Profil</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- POST MATCHES GRID -->
    <div class="sn-explore-grid">
        <?php if (empty($searchResults['posts']) && empty($searchResults['users'])): ?>
            <div style="grid-column:1/-1;padding:80px 20px;text-align:center;">
                <div style="font-size:42px;margin-bottom:12px;color:var(--text-muted);"><?= icon('search', 'w-12 h-12') ?></div>
                <h3 style="font-size:18px;font-weight:800;color:var(--text-main);margin-bottom:6px;">Sonuç Bulunamadı</h3>
                <p style="color:var(--text-muted);font-size:14px;max-width:340px;margin:0 auto 20px;">"<?= e($query) ?>" aramasıyla eşleşen içerik bulunamadı. Başka bir anahtar kelime veya etiket deneyebilirsiniz.</p>
                <a href="<?= url('/explore') ?>" class="btn-primary">Tüm Keşfet'e Dön</a>
            </div>
        <?php elseif (!empty($searchResults['posts'])): ?>
            <?php foreach ($searchResults['posts'] as $post): ?>
                <?php
                    $hasMedia = !empty($post['media']);
                    preg_match('/#([\p{L}\p{N}_]+)/u', $post['content'], $firstTag);
                    $badgeText = !empty($firstTag[1]) ? '#' . $firstTag[1] : '✦ Keşfet';
                ?>
                <div class="sn-explore-card <?= $hasMedia ? 'has-media' : 'is-quote' ?>" id="explore-post-<?= $post['id'] ?>">
                    <?php if ($hasMedia): ?>
                        <div class="sn-explore-img-wrap">
                            <a href="<?= url('/post/' . $post['id']) ?>">
                                <img src="<?= upload($post['media'][0]['file_path']) ?>" loading="lazy" alt="Gönderi Görseli">
                            </a>
                            <span class="sn-explore-tag-badge"><?= e($badgeText) ?></span>
                            <?php if (is_logged_in()): ?>
                            <button class="sn-explore-quick-save <?= !empty($post['user_has_saved']) ? 'saved' : '' ?>" onclick="handleSave(<?= (int)$post['id'] ?>, this)" title="Kaydet">
                                <?= icon('bookmark', 'w-4 h-4') ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="sn-explore-card-body">
                        <?php if (!$hasMedia): ?>
                            <div class="sn-quote-badge">
                                <span>💬</span>
                                <span>GÖNDERİ</span>
                            </div>
                        <?php endif; ?>

                        <div class="sn-explore-caption">
                            <?= nl2br(preg_replace('/#([\p{L}\p{N}_]+)/u', '<a href="' . url('/explore?q=%23$1') . '" class="sn-post-tag">#$1</a>', e($post['content']))) ?>
                        </div>

                        <div class="sn-explore-card-footer">
                            <a href="<?= url('/u/' . $post['username']) ?>" class="sn-explore-author">
                                <?= avatar_img($post['user_avatar'] ?? null, $post['full_name'] ?? $post['username'], 'sn-avatar', 'width:32px;height:32px;') ?>
                                <div style="overflow:hidden;">
                                    <span class="sn-explore-author-name">
                                        <?= e($post['full_name'] ?: $post['username']) ?>
                                        <?php if (!empty($post['is_verified'])): ?>
                                            <span class="badge-verified" title="Doğrulanmış"><?= icon('badge-check', 'w-3 h-3') ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="sn-explore-time"><?= format_time_ago($post['created_at']) ?></span>
                                </div>
                            </a>

                            <div class="sn-explore-stats">
                                <button class="sn-explore-action-btn <?= !empty($post['user_has_liked']) ? 'liked' : '' ?>" onclick="handleLike(<?= (int)$post['id'] ?>, this)" title="Beğen">
                                    <?= icon('heart', 'w-4 h-4') ?>
                                    <span class="count"><?= (int)($post['likes_count'] ?? 0) ?></span>
                                </button>
                                <a href="<?= url('/post/' . $post['id']) ?>" class="sn-explore-action-btn" title="Yorumlar">
                                    <?= icon('chat', 'w-4 h-4') ?>
                                    <span><?= (int)($post['comments_count'] ?? 0) ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- 2. EXPLORE SPOTLIGHT HERO BANNER (When No Search) -->
    <div class="sn-explore-spotlight">
        <div class="sn-spotlight-content">
            <div class="sn-spotlight-badge">
                <span>✦</span>
                <span>HAFTANIN ÖNE ÇIKANLARI</span>
            </div>
            <h1 class="sn-spotlight-title">Görsel İlham &amp; Teknoloji Keşfi</h1>
            <p class="sn-spotlight-desc">Tasarımcıların, yazılımcıların ve yaratıcı profesyonellerin en popüler çalışmalarını keşfedin.</p>
        </div>
        <div class="sn-spotlight-stats">
            <div class="sn-stat-pill">
                <span class="num"><?= count($trendingPosts) ?>+</span>
                <span class="lbl">Popüler İçerik</span>
            </div>
            <div class="sn-stat-pill">
                <span class="num">8</span>
                <span class="lbl">Kategori</span>
            </div>
        </div>
    </div>

    <!-- 3. PINTEREST-STYLE VISUAL DISCOVERY GRID -->
    <div class="sn-explore-grid">
        <?php if (empty($trendingPosts)): ?>
            <div style="grid-column:1/-1;padding:80px 20px;text-align:center;">
                <div style="font-size:42px;margin-bottom:12px;color:var(--text-muted);"><?= icon('explore', 'w-12 h-12') ?></div>
                <h3 style="font-size:18px;font-weight:800;color:var(--text-main);margin-bottom:6px;">Keşfet Akışı Hazırlanıyor</h3>
                <p style="color:var(--text-muted);font-size:14px;max-width:340px;margin:0 auto 20px;">Trend gönderiler ve popüler içerikler bu alanda listelenecek.</p>
            </div>
        <?php else: ?>
            <?php foreach ($trendingPosts as $index => $post): ?>
                <?php
                    $hasMedia = !empty($post['media']);
                    preg_match('/#([\p{L}\p{N}_]+)/u', $post['content'], $firstTag);
                    $badgeText = !empty($firstTag[1]) ? '#' . $firstTag[1] : '✦ Keşfet';
                ?>
                <div class="sn-explore-card <?= $hasMedia ? 'has-media' : 'is-quote' ?>" id="explore-post-<?= $post['id'] ?>">
                    <?php if ($hasMedia): ?>
                        <div class="sn-explore-img-wrap">
                            <a href="<?= url('/post/' . $post['id']) ?>" class="sn-explore-img-link">
                                <img src="<?= upload($post['media'][0]['file_path']) ?>" loading="lazy" alt="Gönderi Medyası">
                            </a>
                            <span class="sn-explore-tag-badge"><?= e($badgeText) ?></span>
                            <?php if (is_logged_in()): ?>
                            <button class="sn-explore-quick-save <?= !empty($post['user_has_saved']) ? 'saved' : '' ?>" onclick="handleSave(<?= (int)$post['id'] ?>, this)" title="Kaydet">
                                <?= icon('bookmark', 'w-4 h-4') ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="sn-explore-card-body">
                        <?php if (!$hasMedia): ?>
                            <div class="sn-quote-badge">
                                <span>💬</span>
                                <span>ÖNE ÇIKAN FİKİR</span>
                            </div>
                        <?php endif; ?>

                        <div class="sn-explore-caption">
                            <?= nl2br(preg_replace('/#([\p{L}\p{N}_]+)/u', '<a href="' . url('/explore?q=%23$1') . '" class="sn-post-tag">#$1</a>', e($post['content']))) ?>
                        </div>

                        <div class="sn-explore-card-footer">
                            <a href="<?= url('/u/' . $post['username']) ?>" class="sn-explore-author">
                                <?= avatar_img($post['user_avatar'] ?? null, $post['full_name'] ?? $post['username'], 'sn-avatar', 'width:32px;height:32px;') ?>
                                <div style="overflow:hidden;">
                                    <span class="sn-explore-author-name">
                                        <?= e($post['full_name'] ?: $post['username']) ?>
                                        <?php if (!empty($post['is_verified'])): ?>
                                            <span class="badge-verified" title="Doğrulanmış"><?= icon('badge-check', 'w-3 h-3') ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="sn-explore-time"><?= format_time_ago($post['created_at']) ?></span>
                                </div>
                            </a>

                            <div class="sn-explore-stats">
                                <button class="sn-explore-action-btn <?= !empty($post['user_has_liked']) ? 'liked' : '' ?>" onclick="handleLike(<?= (int)$post['id'] ?>, this)" title="Beğen">
                                    <?= icon('heart', 'w-4 h-4') ?>
                                    <span class="count"><?= (int)$post['likes_count'] ?></span>
                                </button>
                                <a href="<?= url('/post/' . $post['id']) ?>" class="sn-explore-action-btn" title="Yorumlar">
                                    <?= icon('chat', 'w-4 h-4') ?>
                                    <span><?= (int)$post['comments_count'] ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
