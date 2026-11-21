<div class="sn-widget" style="margin-bottom:20px;border-radius:0;border-left:none;border-right:none;border-top:none;">
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="color:var(--brand-primary);"><?= icon('bookmark', 'w-6 h-6') ?></span>
        <div>
            <h1 style="font-size:20px;font-weight:800;letter-spacing:-0.02em;">Kaydedilen Gönderiler</h1>
            <p style="font-size:13px;color:var(--text-muted);">Yalnızca sizin görebileceğiniz yer işaretleriniz</p>
        </div>
    </div>
</div>

<div class="sn-post-feed">
    <?php if (empty($posts)): ?>
        <div style="padding:60px 20px;text-align:center;">
            <div style="font-size:38px;margin-bottom:12px;color:var(--text-muted);"><?= icon('bookmark', 'w-10 h-10') ?></div>
            <h3 style="font-size:17px;font-weight:700;color:var(--text-primary);margin-bottom:6px;">Henüz kaydedilen gönderi yok</h3>
            <p style="color:var(--text-muted);font-size:13.5px;max-width:320px;margin:0 auto 16px;">İlgini çeken gönderileri kaydet simgesine tıklayarak daha sonra görüntüleyebilirsin.</p>
            <a href="<?= url('/') ?>" class="btn-primary">Akışa Göz At</a>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
        <article class="sn-post" id="post-card-<?= $post['id'] ?>">
            <div class="sn-post-inner">
                <div class="sn-post-header">
                    <div class="sn-post-author">
                        <a href="<?= url('/u/' . $post['username']) ?>">
                            <?= avatar_img($post['user_avatar'] ?? null, $post['full_name'] ?? $post['username'], 'sn-avatar') ?>
                        </a>
                        <div class="sn-post-meta">
                            <div class="sn-post-name-row">
                                <a href="<?= url('/u/' . $post['username']) ?>"><?= e($post['full_name']) ?></a>
                                <?php if ((int)$post['is_verified'] === 1): ?>
                                    <span class="badge-verified"><?= icon('badge-check', 'w-4 h-4') ?></span>
                                <?php endif; ?>
                                <span class="sn-post-handle">@<?= e($post['username']) ?></span>
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
                        <button class="sn-action-btn btn-like-post <?= !empty($post['user_has_liked']) ? 'liked' : '' ?>" data-post-id="<?= $post['id'] ?>">
                            <?= icon('heart', 'w-5 h-5') ?>
                            <span class="like-count"><?= (int)$post['likes_count'] ?></span>
                        </button>
                        <button class="sn-action-btn">
                            <?= icon('chat', 'w-5 h-5') ?>
                            <span><?= (int)$post['comments_count'] ?></span>
                        </button>
                    </div>
                    <button class="sn-action-btn btn-save-post saved" data-post-id="<?= $post['id'] ?>" title="Kaydedilenlerden Çıkar">
                        <?= icon('bookmark', 'w-5 h-5') ?>
                    </button>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
