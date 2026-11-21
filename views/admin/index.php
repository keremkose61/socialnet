<h1 style="font-size:26px;font-weight:800;margin-bottom:24px;">📊 SocialNet Sistem Yönetimi</h1>

<!-- Stats Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;margin-bottom:32px;">
    <div class="widget-card" style="border-left:4px solid var(--primary-purple);">
        <div style="font-size:13px;color:var(--text-muted);font-weight:600;">TOPLAM KULLANICI</div>
        <div style="font-size:32px;font-weight:800;margin-top:6px;"><?= $totalUsers ?></div>
    </div>
    <div class="widget-card" style="border-left:4px solid var(--primary-turquoise);">
        <div style="font-size:13px;color:var(--text-muted);font-weight:600;">TOPLAM GÖNDERİ</div>
        <div style="font-size:32px;font-weight:800;margin-top:6px;"><?= $totalPosts ?></div>
    </div>
    <div class="widget-card" style="border-left:4px solid var(--success);">
        <div style="font-size:13px;color:var(--text-muted);font-weight:600;">TOPLAM YORUM</div>
        <div style="font-size:32px;font-weight:800;margin-top:6px;"><?= $totalComments ?></div>
    </div>
    <div class="widget-card" style="border-left:4px solid var(--danger);">
        <div style="font-size:13px;color:var(--text-muted);font-weight:600;">BEKLEYEN ŞİKÂYETLER</div>
        <div style="font-size:32px;font-weight:800;margin-top:6px;"><?= $totalReports ?></div>
    </div>
</div>

<!-- Users Table -->
<div class="post-card" style="padding:24px;margin-bottom:32px;">
    <h2 style="font-size:18px;font-weight:800;margin-bottom:16px;">Son Kayıt Olan Kullanıcılar</h2>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;text-align:left;">
            <thead>
                <tr style="border-bottom:1px solid var(--border-color);color:var(--text-muted);">
                    <th style="padding:10px;">ID</th>
                    <th style="padding:10px;">Kullanıcı</th>
                    <th style="padding:10px;">E-posta</th>
                    <th style="padding:10px;">Rol</th>
                    <th style="padding:10px;">Durum</th>
                    <th style="padding:10px;">Kayıt Tarihi</th>
                    <th style="padding:10px;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUsers as $u): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 10px;"><?= $u['id'] ?></td>
                    <td style="padding:12px 10px;display:flex;align-items:center;gap:10px;">
                        <?= avatar_img($u['avatar'] ?? null, $u['full_name'] ?? $u['username'], 'sn-avatar', 'width:32px;height:32px;') ?>
                        <div>
                            <div style="font-weight:700;"><?= e($u['full_name']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted);">@<?= e($u['username']) ?></div>
                        </div>
                    </td>
                    <td style="padding:12px 10px;"><?= e($u['email']) ?></td>
                    <td style="padding:12px 10px;">
                        <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;background:<?= ((int)$u['role_id'] === 1) ? 'rgba(139, 92, 246, 0.2)' : 'rgba(255,255,255,0.06)' ?>;">
                            <?= ((int)$u['role_id'] === 1) ? 'Yönetici' : 'Kullanıcı' ?>
                        </span>
                    </td>
                    <td style="padding:12px 10px;">
                        <span id="ban-badge-<?= $u['id'] ?>" style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;background:<?= ((int)$u['is_banned'] === 1) ? 'rgba(239, 68, 68, 0.2);color:#ef4444' : 'rgba(16, 185, 129, 0.2);color:#10b981' ?>;">
                            <?= ((int)$u['is_banned'] === 1) ? 'Yasaklı' : 'Aktif' ?>
                        </span>
                    </td>
                    <td style="padding:12px 10px;color:var(--text-muted);font-size:12px;"><?= date('d.m.Y H:i', strtotime($u['created_at'])) ?></td>
                    <td style="padding:12px 10px;">
                        <?php if ((int)$u['id'] !== (int)auth()['id']): ?>
                        <button onclick="toggleUserBan(<?= $u['id'] ?>)" id="ban-btn-<?= $u['id'] ?>" class="btn-primary" style="padding:4px 12px;font-size:12px;background:<?= ((int)$u['is_banned'] === 1) ? 'var(--success)' : 'var(--danger)' ?>;">
                            <?= ((int)$u['is_banned'] === 1) ? 'Engeli Kaldır' : 'Yasakla' ?>
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function toggleUserBan(userId) {
    if (!confirm('Kullanıcının durumunu değiştirmek istediğinize emin misiniz?')) return;
    try {
        const res = await fetch(`<?= url('/admin/users/') ?>${userId}/ban`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            }
        });
        const data = await res.json();
        if (data.success) {
            const btn = document.getElementById('ban-btn-' + userId);
            const badge = document.getElementById('ban-badge-' + userId);
            if (data.is_banned) {
                btn.textContent = 'Engeli Kaldır';
                btn.style.background = 'var(--success)';
                badge.textContent = 'Yasaklı';
                badge.style.background = 'rgba(239, 68, 68, 0.2)';
                badge.style.color = '#ef4444';
            } else {
                btn.textContent = 'Yasakla';
                btn.style.background = 'var(--danger)';
                badge.textContent = 'Aktif';
                badge.style.background = 'rgba(16, 185, 129, 0.2)';
                badge.style.color = '#10b981';
            }
        }
    } catch (err) {
        console.error(err);
    }
}
</script>
