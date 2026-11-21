/**
 * SocialNet - Elite Frontend App Engine
 * High-performance UI interactions, theme manager, live search, AJAX handlers
 */

// Global Toast System
window.showToast = function(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'sn-toast' + (type === 'error' ? ' error' : '');
    toast.innerHTML = `<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
};

// Theme Toggle
window.toggleTheme = function() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('socialnet_theme', newTheme);
    showToast(newTheme === 'dark' ? 'Koyu tema aktif' : 'Açık tema aktif');
};

// Global Story Viewer Controls
window.viewUserStories = function(userData) {
    if (!userData || !userData.stories || userData.stories.length === 0) return;
    const modal = document.getElementById('story-viewer-modal');
    const imgDisplay = document.getElementById('story-image-display');
    const nameEl = document.getElementById('story-user-name');
    const avatarEl = document.getElementById('story-user-avatar');

    if (nameEl) nameEl.textContent = userData.username;
    if (avatarEl) {
        avatarEl.src = `${window.APP_URL || ''}/uploads/${userData.user_avatar || 'default-avatar.png'}`;
    }
    if (imgDisplay) {
        imgDisplay.src = `${window.APP_URL || ''}/uploads/${userData.stories[0].media_path}`;
    }

    if (modal) modal.classList.add('open');
};

window.closeStoryViewer = function() {
    const modal = document.getElementById('story-viewer-modal');
    if (modal) modal.classList.remove('open');
};

// Global Delete Post
window.deletePost = async function(postId) {
    if (!confirm('Bu gönderiyi silmek istediğinizden emin misiniz?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
        const res = await fetch(`${window.APP_URL || ''}/post/${postId}/delete`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
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
};

// Global Share Post
window.sharePost = function(postId) {
    const url = (window.APP_URL || window.location.origin) + '/post/' + postId;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showToast('Gönderi bağlantısı panoya kopyalandı! 📋');
        }).catch(() => {
            prompt('Gönderi bağlantısı:', url);
        });
    } else {
        prompt('Gönderi bağlantısı:', url);
    }
};

// Global Toggle Comments Box
window.toggleComments = function(postId) {
    const el = document.getElementById('comments-section-' + postId);
    if (el) el.classList.toggle('open');
};

// Global Inline Media Preview
window.previewInlineMedia = function(input) {
    const preview = document.getElementById('feed-media-preview');
    if (!preview) return;
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
            };
            reader.readAsDataURL(file);
        });
    }
};

// Initialize saved theme
(function() {
    const saved = localStorage.getItem('socialnet_theme');
    if (saved && (saved === 'dark' || saved === 'light')) {
        document.documentElement.setAttribute('data-theme', saved);
    }
})();

// Create Post Modal Controls
window.openCreateModal = function() {
    const modal = document.getElementById('create-post-modal');
    if (modal) modal.classList.add('open');
};

window.closeCreateModal = function() {
    const modal = document.getElementById('create-post-modal');
    if (modal) modal.classList.remove('open');
};

window.handleModalMediaPreview = function(input) {
    const zone = document.getElementById('modal-preview-zone');
    if (!zone) return;
    zone.innerHTML = '';
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
                zone.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
};

// Global Follow Handler
window.handleFollow = async function(username, btn) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
        const res = await fetch(`${window.APP_URL || ''}/u/${username}/follow`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        if (res.status === 401) {
            window.location.href = `${window.APP_URL || ''}/login`;
            return;
        }

        const data = await res.json();
        if (data.success) {
            btn.textContent = data.is_following ? 'Takibi Bırak' : 'Takip Et';
            btn.classList.toggle('btn-following', data.is_following);
            if (data.is_following) {
                btn.style.background = 'var(--bg-secondary)';
                btn.style.color = 'var(--text-primary)';
            } else {
                btn.style.background = '';
                btn.style.color = '';
            }
            const counter = document.getElementById('profile-follower-count');
            if (counter) counter.textContent = data.followers_count;
            showToast(data.is_following ? `@${username} takip ediliyor` : `@${username} takipten çıkarıldı`);
        }
    } catch (err) {
        console.error('Follow error:', err);
    }
};

// Global Like Handler
window.handleLike = async function(postId, btn) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
        const res = await fetch(`${window.APP_URL || ''}/post/${postId}/like`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });

        if (res.status === 401) {
            window.location.href = `${window.APP_URL || ''}/login`;
            return;
        }

        const data = await res.json();
        if (data.success) {
            btn.classList.toggle('liked', data.liked);
            const count = btn.querySelector('.count') || btn.querySelector('.like-count');
            if (count) count.textContent = data.likes_count;
        }
    } catch (err) {
        console.error('Like error:', err);
    }
};

// Global Save Handler
window.handleSave = async function(postId, btn) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
        const res = await fetch(`${window.APP_URL || ''}/post/${postId}/save`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        if (res.status === 401) {
            window.location.href = `${window.APP_URL || ''}/login`;
            return;
        }

        const data = await res.json();
        if (data.success) {
            btn.classList.toggle('saved', data.saved);
            showToast(data.saved ? 'Gönderi kaydedildi' : 'Gönderi kaydedilenlerden çıkarıldı');
        }
    } catch (err) {
        console.error('Save error:', err);
    }
};

// Global Comment Handler
window.handleComment = async function(event, postId) {
    event.preventDefault();
    const form = event.target;
    const input = form.querySelector('input[name="comment"]') || form.querySelector('.comment-input');
    const content = input.value.trim();
    if (!content) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    try {
        const res = await fetch(`${window.APP_URL || ''}/post/${postId}/comment`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ content })
        });

        if (res.status === 401) {
            window.location.href = `${window.APP_URL || ''}/login`;
            return;
        }

        const data = await res.json();
        if (data.success && data.comment) {
            input.value = '';
            const list = document.querySelector(`#comments-list-${postId}`);
            if (list) {
                const emptyHint = list.querySelector('.sn-empty-comments');
                if (emptyHint) emptyHint.remove();

                const row = document.createElement('div');
                row.className = 'sn-comment-row';
                row.style.cssText = 'display:flex;gap:12px;margin-bottom:14px;';
                row.innerHTML = `
                    <a href="${window.APP_URL}/u/${data.comment.username}">
                        <img src="${data.comment.avatar}" class="sn-avatar" style="width:32px;height:32px;">
                    </a>
                    <div style="flex:1;background:var(--bg-secondary);padding:10px 14px;border-radius:12px;border:1px solid var(--border-color);">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                            <a href="${window.APP_URL}/u/${data.comment.username}" style="font-weight:700;font-size:13px;color:var(--text-main);">${data.comment.full_name || data.comment.username}</a>
                            <span style="font-size:11px;color:var(--text-muted);">${data.comment.created_at || 'Az önce'}</span>
                        </div>
                        <div style="font-size:13px;color:var(--text-main);line-height:1.4;">${data.comment.content}</div>
                    </div>
                `;
                list.appendChild(row);
            }
            showToast('Yorumunuz paylaşıldı.');
        }
    } catch (err) {
        console.error('Comment error:', err);
    }
};

// DOM Event Bindings
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // Bind Feed Likes
    document.querySelectorAll('.btn-like-post').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const postId = btn.dataset.postId;
            if (postId) handleLike(postId, btn);
        });
    });

    // Bind Feed Saves
    document.querySelectorAll('.btn-save-post').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const postId = btn.dataset.postId;
            if (postId) handleSave(postId, btn);
        });
    });

    // Bind Feed Comments
    document.querySelectorAll('.form-add-comment').forEach(form => {
        form.addEventListener('submit', (e) => {
            const postId = form.dataset.postId;
            if (postId) handleComment(e, postId);
        });
    });

    // Bind Follow buttons
    document.querySelectorAll('.btn-follow-user').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const username = btn.dataset.username;
            if (username) handleFollow(username, btn);
        });
    });

    // Live Header Search
    const searchInput = document.getElementById('global-search-input');
    const searchDropdown = document.getElementById('global-search-dropdown');
    let searchTimeout = null;

    if (searchInput && searchDropdown) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = searchInput.value.trim();
            if (query.length < 2) {
                searchDropdown.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`${window.APP_URL || ''}/explore?q=${encodeURIComponent(query)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    searchDropdown.innerHTML = `
                        <a href="${window.APP_URL}/explore?q=${encodeURIComponent(query)}" style="display:flex;align-items:center;gap:8px;padding:12px 14px;color:var(--brand-accent);font-weight:700;font-size:13px;text-decoration:none;border-bottom:1px solid var(--border-color);">
                            <span>🔍 "${query}" ile ilgili her şeyi ara</span>
                        </a>
                    `;
                    searchDropdown.style.display = 'block';
                } catch (_) {}
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.style.display = 'none';
            }
        });
    }

    // Unread Notifications Background Polling
    const badge = document.getElementById('sidebar-notif-badge');
    const dot = document.getElementById('topbar-notif-dot');
    if (badge || dot) {
        const checkUnread = async () => {
            try {
                const res = await fetch(`${window.APP_URL || ''}/notifications/unread`);
                const data = await res.json();
                if (data.count > 0) {
                    if (badge) {
                        badge.style.display = 'inline-flex';
                        badge.textContent = data.count;
                    }
                    if (dot) dot.style.display = 'block';
                } else {
                    if (badge) badge.style.display = 'none';
                    if (dot) dot.style.display = 'none';
                }
            } catch (_) {}
        };
        checkUnread();
        setInterval(checkUnread, 20000);
    }
});
