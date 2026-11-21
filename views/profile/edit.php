<div class="post-card" style="padding:28px;">
    <h2 style="margin-bottom:20px;font-size:20px;font-weight:800;">Profili Düzenle</h2>

    <form action="<?= url('/profile/edit') ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div style="display:flex;gap:20px;margin-bottom:24px;align-items:center;">
            <img src="<?= upload($currentUser['avatar'] ?? 'default-avatar.png') ?>" class="user-avatar-sm" style="width:72px;height:72px;" id="avatar-preview">
            <div>
                <label class="form-label" style="margin-bottom:4px;">Profil Fotoğrafı</label>
                <input type="file" name="avatar" accept="image/*" onchange="previewImg(this, 'avatar-preview')">
            </div>
        </div>

        <div style="margin-bottom:24px;">
            <label class="form-label">Kapak Görseli</label>
            <input type="file" name="cover_image" accept="image/*" class="form-control">
        </div>

        <div class="form-group">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="full_name" class="form-control" value="<?= e($currentUser['full_name']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Biyografi</label>
            <textarea name="bio" class="form-control" rows="3" placeholder="Kendinizden bahsedin..."><?= e($currentUser['bio'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Konum</label>
            <input type="text" name="location" class="form-control" placeholder="ör. İstanbul, Türkiye" value="<?= e($currentUser['location'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Web Sitesi</label>
            <input type="url" name="website" class="form-control" placeholder="https://..." value="<?= e($currentUser['website'] ?? '') ?>">
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;">
            <button type="submit" class="btn-primary" style="padding:10px 24px;">Değişiklikleri Kaydet</button>
            <a href="<?= url('/u/' . $currentUser['username']) ?>" class="btn-primary" style="background:#1f2937;border:1px solid var(--border-color);color:#fff;padding:10px 20px;">İptal</a>
        </div>
    </form>
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
