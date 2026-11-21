<div class="sn-page-header" style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
    <a href="<?= url('/') ?>" class="sn-topbar-btn" title="Geri Dön">
        <?= icon('arrow-left', 'w-5 h-5') ?>
    </a>
    <div>
        <h1 style="font-size:20px;font-weight:800;color:var(--text-main);">Gönderi</h1>
        <p style="font-size:12px;color:var(--text-muted);"><?= e($post['full_name']) ?> (@<?= e($post['username']) ?>)</p>
    </div>
</div>

<article class="post-card" id="post-<?= (int)$post['id'] ?>">
    <div class="post-header">
        <a href="<?= url('/u/' . $post['username']) ?>">
            <?= avatar_img($post['user_avatar'] ?? null, $post['full_name'] ?? $post['username'], 'sn-avatar') ?>
        </a>
        <div class="post-author-info">
            <div class="post-author-name">
                <a href="<?= url('/u/' . $post['username']) ?>"><?= e($post['full_name'] ?: $post['username']) ?></a>
                <?php if (!empty($post['is_verified'])): ?>
                    <span class="sn-verified-badge" title="Doğrulanmış Hesap"><?= icon('badge-check', 'w-3.5 h-3.5') ?></span>
                <?php endif; ?>
                <span class="post-username">@<?= e($post['username']) ?></span>
            </div>
            <div class="post-timestamp"><?= format_time_ago($post['created_at']) ?></div>
        </div>
    </div>

    <div class="post-content">
        <?= nl2br(e($post['content'])) ?>
    </div>

    <?php if (!empty($post['media'])): ?>
        <div class="post-media-grid" style="margin-top:14px;">
            <?php foreach ($post['media'] as $media): ?>
                <?php if ($media['media_type'] === 'video'): ?>
                    <video controls src="<?= upload($media['file_path']) ?>" class="post-media-item" style="max-height:480px;width:100%;border-radius:12px;background:#000;"></video>
                <?php else: ?>
                    <img src="<?= upload($media['file_path']) ?>" class="post-media-item" style="max-height:480px;width:100%;object-fit:cover;border-radius:12px;" loading="lazy" alt="Gönderi Medyası">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="post-actions" style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border-color);display:flex;align-items:center;gap:20px;">
        <button class="post-action-btn <?= !empty($post['user_has_liked']) ? 'liked' : '' ?>" onclick="handleLike(<?= (int)$post['id'] ?>, this)">
            <?= icon('heart', 'w-5 h-5') ?>
            <span class="count"><?= (int)($post['likes_count'] ?? 0) ?></span>
        </button>

        <div class="post-action-btn" style="cursor:default;">
            <?= icon('chat', 'w-5 h-5') ?>
            <span class="count"><?= (int)($post['comments_count'] ?? 0) ?></span>
        </div>

        <button class="post-action-btn <?= !empty($post['user_has_saved']) ? 'saved' : '' ?>" onclick="handleSave(<?= (int)$post['id'] ?>, this)" style="margin-left:auto;">
            <?= icon('bookmark', 'w-5 h-5') ?>
        </button>
    </div>

    <!-- COMMENTS SECTION -->
    <div class="post-comments-section" style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-color);">
        <h3 style="font-size:14px;font-weight:700;color:var(--text-main);margin-bottom:12px;">Yorumlar (<?= count($post['comments'] ?? []) ?>)</h3>

        <?php if (is_logged_in()): ?>
            <form onsubmit="handleComment(event, <?= (int)$post['id'] ?>)" style="display:flex;gap:10px;margin-bottom:20px;">
                <?= avatar_img(auth()['avatar'] ?? null, auth()['full_name'] ?? auth()['username'], 'sn-avatar', 'width:36px;height:36px;') ?>
                <input type="text" name="comment" class="sn-comment-input" placeholder="Düşünceni paylaş..." required autocomplete="off">
                <button type="submit" class="btn-primary" style="padding:8px 16px;font-size:13px;">Gönder</button>
            </form>
        <?php endif; ?>

        <div class="post-comments-list" id="comments-list-<?= (int)$post['id'] ?>">
            <?php if (empty($post['comments'])): ?>
                <div class="sn-empty-comments" style="padding:24px 0;text-align:center;color:var(--text-muted);font-size:13px;">
                    İlk yorumu sen yap!
                </div>
            <?php else: ?>
                <?php foreach ($post['comments'] as $comm): ?>
                    <div class="sn-comment-row" style="display:flex;gap:12px;margin-bottom:14px;">
                        <a href="<?= url('/u/' . $comm['username']) ?>">
                            <?= avatar_img($comm['user_avatar'] ?? null, $comm['full_name'] ?? $comm['username'], 'sn-avatar', 'width:32px;height:32px;') ?>
                        </a>
                        <div style="flex:1;background:var(--bg-secondary);padding:10px 14px;border-radius:12px;border:1px solid var(--border-color);">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <a href="<?= url('/u/' . $comm['username']) ?>" style="font-weight:700;font-size:13px;color:var(--text-main);"><?= e($comm['full_name'] ?: $comm['username']) ?></a>
                                    <?php if (!empty($comm['is_verified'])): ?>
                                        <span class="sn-verified-badge"><?= icon('badge-check', 'w-3 h-3') ?></span>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size:11px;color:var(--text-muted);"><?= format_time_ago($comm['created_at']) ?></span>
                            </div>
                            <div style="font-size:13px;color:var(--text-main);line-height:1.4;"><?= nl2br(e($comm['content'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</article>
