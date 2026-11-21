<!-- 1. STORIES CAROUSEL -->
<div class="sn-stories-bar">
    <?php if (is_logged_in()): $me = auth(); ?>
    <div class="sn-story-card" onclick="document.getElementById('story-file-input').click()">
        <form id="story-upload-form" action="<?= url('/stories/create') ?>" method="POST" enctype="multipart/form-data" style="display:none;">
            <?= csrf_field() ?>
            <input type="file" name="story_media" id="story-file-input" accept="image/*,video/*" onchange="document.getElementById('story-upload-form').submit()">
        </form>
        <div class="sn-story-ring" style="background:var(--border-color);">
            <?= avatar_img($me['avatar'] ?? null, $me['full_name'] ?? $me['username'], 'sn-story-avatar') ?>
            <span class="sn-story-plus">+</span>
        </div>
        <span class="sn-story-label">Hikâyen</span>
    </div>
    <?php endif; ?>

    <?php if (!empty($stories)): ?>
        <?php foreach ($stories as $stUser): ?>
        <div class="sn-story-card" onclick="viewUserStories(<?= htmlspecialchars(json_encode($stUser), ENT_QUOTES, 'UTF-8') ?>)">
            <div class="sn-story-ring">
                <?= avatar_img($stUser['user_avatar'] ?? null, $stUser['username'], 'sn-story-avatar') ?>
            </div>
            <span class="sn-story-label"><?= e($stUser['username']) ?></span>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- 2. POST COMPOSE CARD (When Logged In) -->
<?php if (is_logged_in()): $me = auth(); ?>
<div class="sn-compose-card">
    <form action="<?= url('/post/create') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="sn-compose-top">
            <?= avatar_img($me['avatar'] ?? null, $me['full_name'] ?? $me['username'], 'sn-avatar') ?>
            <textarea name="content" class="sn-compose-textarea" placeholder="<?= e($me['full_name'] ?: $me['username']) ?>, neler düşünüyorsun? #hashtag veya @kullanıcı ekle..."></textarea>
        </div>

        <div id="feed-media-preview" style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap;"></div>

        <div class="sn-compose-bottom">
            <div class="sn-compose-tools">
                <label class="btn-tool" style="cursor:pointer;" title="Fotoğraf veya Video Ekle">
                    <?= icon('image', 'w-4 h-4') ?>
                    <span>Medya</span>
                    <input type="file" name="media[]" id="inline-media-input" multiple accept="image/*,video/*" style="display:none;" onchange="previewInlineMedia(this)">
                </label>
                <select name="visibility" class="sn-select-privacy">
                    <option value="public">🌍 Herkese Açık</option>
                    <option value="followers">👥 Yalnızca Takipçiler</option>
                    <option value="private">🔒 Yalnızca Ben</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <?= icon('send', 'w-4 h-4') ?>
                <span>Paylaş</span>
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 3. POSTS FEED STREAM -->
<div class="sn-post-feed" id="post-feed-container">
    <?php if (empty($posts)): ?>
        <div style="padding:60px 20px;text-align:center;">
            <div style="font-size:42px;margin-bottom:12px;color:var(--brand-secondary);"><?= icon('sparkles', 'w-12 h-12') ?></div>
            <h3 style="font-size:18px;font-weight:800;color:var(--text-primary);margin-bottom:6px;">Akışın Hazır</h3>
            <p style="color:var(--text-muted);font-size:14px;max-width:320px;margin:0 auto 20px;">İlk gönderini paylaşarak veya yeni kişileri takip ederek akışını hareketlendir!</p>
            <?php if (is_logged_in()): ?>
            <button class="btn-primary" onclick="openCreateModal()">İlk Gönderini Paylaş</button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
        <article class="sn-post" id="post-card-<?= $post['id'] ?>">
            <div class="sn-post-inner">
                <!-- Post Header -->
                <div class="sn-post-header">
                    <div class="sn-post-author">
                        <a href="<?= url('/u/' . $post['username']) ?>">
                            <?= avatar_img($post['user_avatar'] ?? null, $post['full_name'] ?? $post['username'], 'sn-avatar') ?>
                        </a>
                        <div class="sn-post-meta">
                            <div class="sn-post-name-row">
                                <a href="<?= url('/u/' . $post['username']) ?>" style="color:var(--text-primary);font-weight:700;"><?= e($post['full_name'] ?: $post['username']) ?></a>
                                <?php if ((int)$post['is_verified'] === 1): ?>
                                    <span class="badge-verified" title="Doğrulanmış Hesap"><?= icon('badge-check', 'w-4 h-4') ?></span>
                                <?php endif; ?>
                                <span class="sn-post-handle">@<?= e($post['username']) ?></span>
                            </div>
                            <span class="sn-post-time"><?= format_time_ago($post['created_at']) ?></span>
                        </div>
                    </div>

                    <?php if (is_logged_in() && (auth()['id'] === (int)$post['user_id'] || (int)auth()['role_id'] === 1)): ?>
                    <button onclick="deletePost(<?= $post['id'] ?>)" class="btn-tool" style="color:var(--text-muted);padding:6px;" title="Gönderiyi Sil">
                        <?= icon('trash', 'w-4 h-4') ?>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Post Text Content -->
                <?php if (!empty($post['content'])): ?>
                <div class="sn-post-content">
                    <?= nl2br(preg_replace('/#([\p{L}\p{N}_]+)/u', '<a href="' . url('/explore?q=%23$1') . '">#$1</a>', e($post['content']))) ?>
                </div>
                <?php endif; ?>

                <!-- Post Media Attachments -->
                <?php if (!empty($post['media'])): ?>
                <div class="sn-post-media">
                    <?php foreach ($post['media'] as $med): ?>
                        <?php if ($med['file_type'] === 'video'): ?>
                            <video src="<?= upload($med['file_path']) ?>" controls></video>
                        <?php else: ?>
                            <img src="<?= upload($med['file_path']) ?>" loading="lazy" alt="Gönderi Medyası">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Post Action Buttons -->
                <div class="sn-post-actions">
                    <div class="sn-action-group">
                        <!-- Like Button -->
                        <button class="sn-action-btn btn-like-post <?= !empty($post['user_has_liked']) ? 'liked' : '' ?>" data-post-id="<?= $post['id'] ?>">
                            <?= icon('heart', 'w-5 h-5') ?>
                            <span class="like-count"><?= (int)$post['likes_count'] ?></span>
                        </button>

                        <!-- Comments Toggle Button -->
                        <button class="sn-action-btn" onclick="toggleComments(<?= $post['id'] ?>)">
                            <?= icon('chat', 'w-5 h-5') ?>
                            <span id="comment-count-<?= $post['id'] ?>"><?= (int)$post['comments_count'] ?></span>
                        </button>

                        <!-- Share Button -->
                        <button class="sn-action-btn" onclick="sharePost(<?= $post['id'] ?>)" title="Bağlantıyı Kopyala">
                            <?= icon('share', 'w-5 h-5') ?>
                        </button>
                    </div>

                    <!-- Bookmark / Save Button -->
                    <button class="sn-action-btn btn-save-post <?= !empty($post['user_has_saved']) ? 'saved' : '' ?>" data-post-id="<?= $post['id'] ?>" title="Kaydet">
                        <?= icon('bookmark', 'w-5 h-5') ?>
                    </button>
                </div>

                <!-- Collapsible Comments Section -->
                <div class="sn-comments-box" id="comments-section-<?= $post['id'] ?>">
                    <div id="comments-list-<?= $post['id'] ?>">
                        <!-- Dynamic comments inserted here -->
                    </div>

                    <?php if (is_logged_in()): ?>
                    <form class="sn-comment-form form-add-comment" data-post-id="<?= $post['id'] ?>">
                        <?= avatar_img(auth()['avatar'] ?? null, auth()['full_name'] ?? auth()['username'], 'sn-avatar', 'width:32px;height:32px;') ?>
                        <input type="text" class="sn-comment-input comment-input" placeholder="Bir yanıt yazın..." required autocomplete="off">
                        <button type="submit" class="btn-primary" style="padding:6px 16px;font-size:12.5px;">Yanıtla</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function previewInlineMedia(input) {
    const preview = document.getElementById('feed-media-preview');
    preview.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '70px';
                img.style.height = '70px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                img.style.border = '1px solid var(--border-color)';
                preview.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    }
}

function toggleComments(postId) {
    const el = document.getElementById('comments-section-' + postId);
    if (el) {
        el.classList.toggle('open');
    }
}

function sharePost(postId) {
    const dummy = document.createElement('input');
    dummy.value = window.location.origin + window.APP_URL + '/post/' + postId;
    document.body.appendChild(dummy);
    dummy.select();
    document.execCommand('copy');
    document.body.removeChild(dummy);
    showToast('Gönderi bağlantısı panoya kopyalandı!');
}

async function deletePost(postId) {
    if (!confirm('Bu gönderiyi silmek istediğinizden emin misiniz?')) return;
    try {
        const res = await fetch(`${window.APP_URL}/post/${postId}/delete`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            }
        });
        const data = await res.json();
        if (data.success) {
            const card = document.getElementById('post-card-' + postId);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(10px)';
                setTimeout(() => card.remove(), 250);
            }
            showToast('Gönderi başarıyla silindi.');
        }
    } catch (err) {
        console.error(err);
    }
}

function viewUserStories(userData) {
    if (!userData || !userData.stories || userData.stories.length === 0) return;
    const modal = document.getElementById('story-viewer-modal');
    const imgDisplay = document.getElementById('story-image-display');
    const nameEl = document.getElementById('story-user-name');
    const avatarEl = document.getElementById('story-user-avatar');

    nameEl.textContent = userData.username;
    if (avatarEl) {
        avatarEl.src = `${window.APP_URL}/uploads/${userData.user_avatar || 'default-avatar.png'}`;
    }
    imgDisplay.src = `${window.APP_URL}/uploads/${userData.stories[0].media_path}`;

    modal.classList.add('open');
}

function closeStoryViewer() {
    document.getElementById('story-viewer-modal').classList.remove('open');
}
</script>
