<form action="<?= url('/register') ?>" method="POST">
    <?= csrf_field() ?>
    
    <div class="form-group">
        <label class="form-label" for="full_name">Ad Soyad</label>
        <input type="text" name="full_name" id="full_name" class="form-control" placeholder="ör. Ahmet Yılmaz" required value="<?= e(old('full_name')) ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="username">Kullanıcı Adı</label>
        <input type="text" name="username" id="username" class="form-control" placeholder="ör. ahmetyilmaz" required value="<?= e(old('username')) ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="email">E-posta Adresi</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="ör. ahmet@ornek.com" required value="<?= e(old('email')) ?>">
    </div>

    <div class="form-group">
        <label class="form-label" for="password">Şifre (En az 6 karakter)</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
    </div>

    <div class="form-group">
        <label class="form-label" for="password_confirm">Şifre Tekrar</label>
        <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn-primary btn-submit">Hesap Oluştur</button>

    <div class="auth-footer">
        Zaten hesabınız var mı? <a href="<?= url('/login') ?>">Giriş Yap</a>
    </div>
</form>
