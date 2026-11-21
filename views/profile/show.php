<div class="sn-profile-header">
    <!-- Cover Image -->
    <div class="sn-cover-container">
        <img src="<?= upload($profileUser['cover_image'] ?? 'default-cover.jpg') ?>" class="sn-cover-img" alt="Kapak Görseli">
    </div>

    <!-- Profile Details Body -->
    <div class="sn-profile-body">
        <div class="sn-avatar-row">
            <?= avatar_img($profileUser['avatar'] ?? null, $profileUser['full_name'] ?? $profileUser['username'], 'sn-profile-avatar-lg') ?>

            <div style="display:flex;gap:10px;margin-bottom:8px;">
                <?php if ($isSelf): ?>
                    <a href="<?= url('/settings') ?>" class="btn-secondary" style="display:flex;align-items:center;gap:6px;">
                        <?= icon('settings', 'w-4 h-4') ?>
                        <span>Profili Düzenle</span>
                    </a>
                <?php elseif (is_logged_in()): ?>
                    <a href="<?= url('/messages/start/' . $profileUser['username']) ?>" class="btn-secondary" style="display:flex;align-items:center;gap:6px;">
                        <?= icon('message', 'w-4 h-4') ?>
                        <span>Mesaj</span>
                    </a>
                    <button class="btn-primary btn-follow-user <?= $isFollowing ? 'btn-following' : '' ?>" data-username="<?= e($profileUser['username']) ?>">
                        <?= $isFollowing ? 'Takibi Bırak' : 'Takip Et' ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="sn-profile-meta">
            <h1>
                <span><?= e($profileUser['full_name']) ?></span>
                <?php if ((int)$profileUser['is_verified'] === 1): ?>
                    <span class="badge-verified"><?= icon('badge-check', 'w-5 h-5') ?></span>
                <?php endif; ?>
            </h1>
            <div class="sn-profile-handle">@<?= e($profileUser['username']) ?></div>

            <?php if (!empty($profileUser['bio'])): ?>
                <p class="sn-profile-bio"><?= nl2br(e($profileUser['bio'])) ?></p>
            <?php endif; ?>

            <div class="sn-profile-info-row">
                <?php if (!empty($profileUser['location'])): ?>
                    <div class="sn-profile-info-item">
                        <?= icon('map-pin', 'w-4 h-4') ?>
                        <span><?= e($profileUser['location']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profileUser['website'])): ?>
                    <div class="sn-profile-info-item">
                        <?= icon('link', 'w-4 h-4') ?>
                        <a href="<?= e($profileUser['website']) ?>" target="_blank" rel="noopener" style="color:var(--brand-accent);font-weight:600;"><?= e($profileUser['website']) ?></a>
                    </div>
                <?php endif; ?>
                <div class="sn-profile-info-item">
                    <?= icon('calendar', 'w-4 h-4') ?>
                    <span><?= date('M Y', strtotime($profileUser['created_at'])) ?> tarihinde katıldı</span>
                </div>
            </div>

            <div class="sn-stats-pills">
                <div class="sn-stat">
                    <span class="sn-stat-count"><?= $counts['posts'] ?></span>
                    <span class="sn-stat-label">Gönderi</span>
                </div>
                <div class="sn-stat">
                    <span class="sn-stat-count" id="profile-follower-count"><?= $counts['followers'] ?></span>
                    <span class="sn-stat-label">Takipçi</span>
                </div>
                <div class="sn-stat">
                    <span class="sn-stat-count"><?= $counts['following'] ?></span>
                    <span class="sn-stat-label">Takip Edilen</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Tabs -->
    <div class="sn-tabs">
        <div class="sn-tab-item active" onclick="switchProfileTab('posts', this)">
            <?= icon('sparkles', 'w-4 h-4') ?>
            <span>Gönderiler</span>
        </div>
        <div class="sn-tab-item" onclick="switchProfileTab('media', this)">
            <?= icon('image', 'w-4 h-4') ?>
            <span>Fotoğraf & Video</span>
        </div>
        <?php if ($isSelf): ?>
        <div class="sn-tab-item" onclick="switchProfileTab('saved', this)">
            <?= icon('bookmark', 'w-4 h-4') ?>
            <span>Kaydedilenler</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tab Content: Posts Stream -->
<div class="sn-post-feed" id="tab-posts-container">
    <?php if (empty($posts)): ?>
        <div style="padding:60px 20px;text-align:center;color:var(--text-muted);">
            Henüz paylaşılmış bir gönderi bulunmuyor.
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
        <article class="sn-post" id="post-card-<?= $post['id'] ?>">
            <div class="sn-post-inner">
                <div class="sn-post-header">
                    <div class="sn-post-author">
                        <?= avatar_img($profileUser['avatar'] ?? null, $profileUser['full_name'] ?? $profileUser['username'], 'sn-avatar') ?>
                        <div class="sn-post-meta">
                            <div class="sn-post-name-row">
                                <span><?= e($profileUser['full_name']) ?></span>
                                <span class="sn-post-handle">@<?= e($profileUser['username']) ?></span>
                            </div>
                            <span class="sn-post-time"><?= format_time_ago($post['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($post['content'])): ?>
                <div class="sn-post-content">
                    <?= nl2br(preg_replace('/#([\p{L}\p{N}_]+)/u', '<a href="' . url('/explore?q=%23$1') . '">#$1</a>', e($post['content']))) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($post['media'])): ?>
                <div class="sn-post-media">
                    <?php foreach ($post['media'] as $med): ?>
                        <img src="<?= upload($med['file_path']) ?>" loading="lazy" alt="Gönderi Medyası">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="sn-post-actions">
                    <div class="sn-action-group">
                        <button class="sn-action-btn btn-like-post <?= !empty($post['user_has_liked']) ? 'liked' : '' ?>" data-post-id="<?= $post['id'] ?>" onclick="handleLike(<?= (int)$post['id'] ?>, this)">
                            <?= icon('heart', 'w-5 h-5') ?>
                            <span class="like-count"><?= (int)$post['likes_count'] ?></span>
                        </button>
                        <a href="<?= url('/post/' . $post['id']) ?>" class="sn-action-btn">
                            <?= icon('chat', 'w-5 h-5') ?>
                            <span><?= (int)$post['comments_count'] ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Tab Content: Media Gallery Grid -->
<div id="tab-media-container" style="display:none;padding:20px;">
    <?php
    $mediaPosts = array_filter($posts, fn($p) => !empty($p['media']));
    if (empty($mediaPosts)):
    ?>
        <div style="padding:60px 20px;text-align:center;color:var(--text-muted);">
            Paylaşılmış fotoğraf veya video bulunamadı.
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:14px;">
            <?php foreach ($mediaPosts as $mp): ?>
                <a href="<?= url('/post/' . $mp['id']) ?>" class="sn-media-tile" style="position:relative;aspect-ratio:1/1;border-radius:12px;overflow:hidden;background:#09090F;border:1px solid var(--border-color);display:block;">
                    <img src="<?= upload($mp['media'][0]['file_path']) ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s ease;" onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'" alt="Medya">
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);opacity:0;display:flex;align-items:center;justify-content:center;gap:16px;color:#fff;font-weight:700;font-size:14px;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                        <span style="display:flex;align-items:center;gap:4px;">❤️ <?= (int)$mp['likes_count'] ?></span>
                        <span style="display:flex;align-items:center;gap:4px;">💬 <?= (int)$mp['comments_count'] ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function switchProfileTab(tab, element) {
    document.querySelectorAll('.sn-tab-item').forEach(el => el.classList.remove('active'));
    element.classList.add('active');

    const postsTab = document.getElementById('tab-posts-container');
    const mediaTab = document.getElementById('tab-media-container');

    if (tab === 'posts') {
        if (postsTab) postsTab.style.display = 'block';
        if (mediaTab) mediaTab.style.display = 'none';
    } else if (tab === 'media') {
        if (postsTab) postsTab.style.display = 'none';
        if (mediaTab) mediaTab.style.display = 'block';
    } else if (tab === 'saved') {
        window.location.href = '<?= url('/saved') ?>';
    }
}
</script>
