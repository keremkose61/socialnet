<form action="<?= url('/login') ?>" method="POST">
    <?= csrf_field() ?>
    
    <div class="form-group">
        <label class="form-label" for="login">Kullanıcı Adı veya E-posta</label>
        <input type="text" name="login" id="login" class="form-control" placeholder="ör. admin veya adiniz@mail.com" required autofocus value="<?= e(old('login')) ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Şifre</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn-primary btn-submit">Giriş Yap</button>

    <div class="auth-footer">
        Hesabınız yok mu? <a href="<?= url('/register') ?>">Hemen Kayıt Ol</a>
    </div>

    <div style="margin-top:20px;padding:12px;background:rgba(255,255,255,0.03);border:1px dashed var(--border-color);border-radius:var(--radius-sm);font-size:12px;color:var(--text-muted);text-align:center;">
        <strong>Demo Yönetici Girişi:</strong><br>
        Kullanıcı: <code>admin</code> | Şifre: <code>Admin12345!</code>
    </div>
</form>
