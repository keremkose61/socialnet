<div class="sn-widget" style="margin-bottom:20px;border-radius:0;border-left:none;border-right:none;border-top:none;">
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="color:var(--brand-primary);"><?= icon('bell', 'w-6 h-6') ?></span>
            <div>
                <h1 style="font-size:20px;font-weight:800;letter-spacing:-0.02em;">Bildirimler</h1>
                <p style="font-size:13px;color:var(--text-muted);">Sizinle olan tüm etkileşimler ve aktiviteler</p>
            </div>
        </div>
        <?php if (!empty($notifications)): ?>
            <button class="btn-secondary" style="font-size:12px;padding:6px 14px;" onclick="showToast('Tüm bildirimler okundu olarak işaretlendi.')">
                <?= icon('check', 'w-3.5 h-3.5') ?>
                <span>Tümünü Okundu Say</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Category Tabs -->
<div class="sn-explore-categories" style="margin-bottom:16px;">
    <button class="sn-category-chip notif-filter active" onclick="filterNotifs('all', this)">Tümü</button>
    <button class="sn-category-chip notif-filter" onclick="filterNotifs('like_post', this)">❤️ Beğeniler</button>
    <button class="sn-category-chip notif-filter" onclick="filterNotifs('comment_post', this)">💬 Yorumlar</button>
    <button class="sn-category-chip notif-filter" onclick="filterNotifs('follow', this)">👥 Takipler</button>
    <button class="sn-category-chip notif-filter" onclick="filterNotifs('message', this)">✉️ Mesajlar</button>
</div>

<div class="sn-notifications-container" style="display:flex;flex-direction:column;gap:10px;">
    <?php if (empty($notifications)): ?>
        <div style="padding:60px 20px;text-align:center;">
            <div style="font-size:42px;margin-bottom:12px;color:var(--text-muted);"><?= icon('bell', 'w-10 h-10') ?></div>
            <h3 style="font-size:17px;font-weight:700;color:var(--text-primary);margin-bottom:6px;">Yeni bildirim yok</h3>
            <p style="color:var(--text-muted);font-size:13.5px;max-width:320px;margin:0 auto 16px;">Takip ettiğiniz kişiler veya gönderilerinizle etkileşim olduğunda burada göreceksiniz.</p>
            <a href="<?= url('/explore') ?>" class="btn-primary">Yeni Kişiler Keşfet</a>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
        <div class="sn-notif-card notif-type-<?= e($notif['type']) ?>" style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-radius:var(--radius-md);background:var(--bg-card);border:1px solid var(--border-color);transition:var(--transition);">
            <div style="position:relative;">
                <a href="<?= url('/u/' . $notif['username']) ?>">
                    <?= avatar_img($notif['sender_avatar'] ?? null, $notif['full_name'] ?? $notif['username'], 'sn-avatar', 'width:44px;height:44px;') ?>
                </a>
                <span style="position:absolute;bottom:-2px;right:-2px;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:var(--bg-card);border:2px solid var(--bg-card);font-size:11px;">
                    <?php
                        switch ($notif['type']) {
                            case 'like_post': echo '❤️'; break;
                            case 'comment_post': echo '💬'; break;
                            case 'follow': echo '👥'; break;
                            case 'message': echo '✉️'; break;
                            default: echo '⚡'; break;
                        }
                    ?>
                </span>
            </div>

            <div style="flex:1;min-width:0;">
                <div style="font-size:14px;color:var(--text-main);line-height:1.4;">
                    <a href="<?= url('/u/' . $notif['username']) ?>" style="font-weight:700;color:var(--text-main);text-decoration:none;">
                        <?= e($notif['full_name'] ?: $notif['username']) ?>
                    </a>
                    <span style="color:var(--text-muted);">
                        <?php
                            switch ($notif['type']) {
                                case 'follow': echo " seni takip etmeye başladı."; break;
                                case 'like_post': echo " gönderini beğendi."; break;
                                case 'comment_post': echo " gönderine bir yorum yaptı."; break;
                                case 'reply_comment': echo " yorumuna yanıt verdi."; break;
                                case 'message': echo " sana yeni bir mesaj gönderdi."; break;
                                default: echo " seninle etkileşime geçti."; break;
                            }
                        ?>
                    </span>
                </div>
                <div style="font-size:11.5px;color:var(--text-muted);margin-top:3px;">
                    <?= format_time_ago($notif['created_at']) ?>
                </div>
            </div>

            <div>
                <?php if ($notif['type'] === 'follow'): ?>
                    <a href="<?= url('/u/' . $notif['username']) ?>" class="btn-primary" style="padding:6px 14px;font-size:12px;">Profili Gör</a>
                <?php elseif ($notif['type'] === 'message'): ?>
                    <a href="<?= url('/messages') ?>" class="btn-secondary" style="padding:6px 14px;font-size:12px;">Sohbete Git</a>
                <?php elseif (!empty($notif['entity_id'])): ?>
                    <a href="<?= url('/post/' . $notif['entity_id']) ?>" class="btn-secondary" style="padding:6px 14px;font-size:12px;">Gönderiyi Aç</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function filterNotifs(type, btn) {
    document.querySelectorAll('.notif-filter').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');

    const cards = document.querySelectorAll('.sn-notif-card');
    cards.forEach(card => {
        if (type === 'all' || card.classList.contains('notif-type-' + type)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
