<div style="padding:24px 28px;border-bottom:1px solid var(--border-color);background:var(--bg-card);">
    <div style="display:flex;align-items:center;gap:12px;">
        <span style="color:var(--brand-primary);"><?= icon('settings', 'w-6 h-6') ?></span>
        <div>
            <h1 style="font-size:22px;font-weight:800;letter-spacing:-0.02em;">Hesap ve Tercihler</h1>
            <p style="font-size:13px;color:var(--text-muted);">Profilinizi, gizlilik ve arayüz seçeneklerinizi yönetin</p>
        </div>
    </div>
</div>

<div style="padding:24px 28px;">
    <!-- Section 1: Profil Bilgileri -->
    <div class="sn-widget" style="margin-bottom:28px;">
        <div class="sn-widget-header">
            <?= icon('user', 'w-4 h-4') ?>
            <span>Profil Bilgileri</span>
        </div>

        <form action="<?= url('/settings') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="tab" value="profile">

            <!-- Avatar & Cover Previews -->
            <div style="display:flex;gap:20px;align-items:center;margin-bottom:20px;">
                <?= avatar_img($currentUser['avatar'] ?? null, $currentUser['full_name'] ?? $currentUser['username'], 'sn-profile-avatar-lg', 'width:84px;height:84px;', 'id="avatar-preview"') ?>
                <div>
                    <label class="btn-secondary" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-size:13px;padding:6px 14px;">
                        <?= icon('camera', 'w-4 h-4') ?>
                        <span>Fotoğrafı Değiştir</span>
                        <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="previewImg(this, 'avatar-preview')">
                    </label>
                    <div style="font-size:11.5px;color:var(--text-muted);margin-top:6px;">Önerilen boyut: 400x400 JPG veya PNG</div>
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Kapak Görseli</label>
                <input type="file" name="cover_image" accept="image/*" class="sn-search-input" style="border-radius:var(--radius-sm);padding:8px 12px;">
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Ad Soyad</label>
                <input type="text" name="full_name" class="sn-search-input" style="border-radius:var(--radius-sm);padding:10px 14px;" value="<?= e($currentUser['full_name']) ?>" required>
            </div>

            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Biyografi</label>
                <textarea name="bio" class="sn-search-input" style="border-radius:var(--radius-sm);padding:10px 14px;min-height:80px;resize:vertical;" placeholder="Kendinizden bahsedin..."><?= e($currentUser['bio'] ?? '') ?></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Konum</label>
                    <input type="text" name="location" class="sn-search-input" style="border-radius:var(--radius-sm);padding:10px 14px;" placeholder="ör. İstanbul, Türkiye" value="<?= e($currentUser['location'] ?? '') ?>">
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Web Sitesi</label>
                    <input type="url" name="website" class="sn-search-input" style="border-radius:var(--radius-sm);padding:10px 14px;" placeholder="https://..." value="<?= e($currentUser['website'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="btn-primary">Değişiklikleri Kaydet</button>
        </form>
    </div>

    <!-- Section 2: Görünüm ve Tema -->
    <div class="sn-widget" style="margin-bottom:28px;">
        <div class="sn-widget-header">
            <?= icon('sparkles', 'w-4 h-4') ?>
            <span>Görünüm ve Tema</span>
        </div>

        <form action="<?= url('/settings') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="tab" value="appearance">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <label style="border:2px solid <?= ($currentUser['theme'] ?? 'dark') === 'dark' ? 'var(--brand-primary)' : 'var(--border-color)' ?>;padding:16px;border-radius:var(--radius-md);background:#09090F;cursor:pointer;display:flex;align-items:center;gap:12px;">
                    <input type="radio" name="theme" value="dark" <?= ($currentUser['theme'] ?? 'dark') === 'dark' ? 'checked' : '' ?> style="accent-color:var(--brand-primary);">
                    <div>
                        <div style="font-weight:700;color:#fff;">Koyu Tema (Önerilen)</div>
                        <div style="font-size:12px;color:#9292A5;">Derin obsidian siyahı ve mor vurgular</div>
                    </div>
                </label>

                <label style="border:2px solid <?= ($currentUser['theme'] ?? '') === 'light' ? 'var(--brand-primary)' : 'var(--border-color)' ?>;padding:16px;border-radius:var(--radius-md);background:#F8FAFC;cursor:pointer;display:flex;align-items:center;gap:12px;">
                    <input type="radio" name="theme" value="light" <?= ($currentUser['theme'] ?? '') === 'light' ? 'checked' : '' ?> style="accent-color:var(--brand-primary);">
                    <div>
                        <div style="font-weight:700;color:#0F172A;">Açık Tema</div>
                        <div style="font-size:12px;color:#64748B;">Temiz, ferah slate beyazı</div>
                    </div>
                </label>
            </div>

            <button type="submit" class="btn-primary">Temayı Uygula</button>
        </form>
    </div>

    <!-- Section 3: Şifre ve Güvenlik -->
    <div class="sn-widget">
        <div class="sn-widget-header">
            <?= icon('shield', 'w-4 h-4') ?>
            <span>Şifre ve Güvenlik</span>
        </div>

        <form action="<?= url('/settings') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="tab" value="security">

            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Mevcut Şifre</label>
                <input type="password" name="current_password" class="sn-search-input" style="border-radius:var(--radius-sm);padding:10px 14px;" placeholder="••••••••" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Yeni Şifre</label>
                    <input type="password" name="new_password" class="sn-search-input" style="border-radius:var(--radius-sm);padding:10px 14px;" placeholder="En az 6 karakter" required>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">Yeni Şifre Tekrar</label>
                    <input type="password" name="confirm_password" class="sn-search-input" style="border-radius:var(--radius-sm);padding:10px 14px;" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-primary">Şifreyi Güncelle</button>
        </form>
    </div>
</div>

<script>
function previewImg(input, targetId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(targetId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
