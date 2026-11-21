<!DOCTYPE html>
<html lang="tr" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($pageTitle ?? 'Giriş Yap – SocialNet') ?></title>
    <link rel="stylesheet" href="<?= asset('css/style.css?v=2.1') ?>">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: radial-gradient(circle at 20% 10%, rgba(139, 92, 246, 0.12), transparent 45%),
                        radial-gradient(circle at 80% 90%, rgba(34, 211, 238, 0.08), transparent 45%),
                        #09090F;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #12121C;
            border: 1px solid #29293A;
            border-radius: var(--radius-lg);
            padding: 36px 32px;
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.7);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            background: #191927;
            border: 1px solid #29293A;
            color: #F8FAFC;
            padding: 11px 15px;
            border-radius: var(--radius-md);
            font-size: 14px;
            outline: none;
            transition: var(--transition);
        }
        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            background: #1e1e30;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            font-size: 14.5px;
            margin-top: 8px;
        }
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
        }
        .auth-footer a {
            color: var(--brand-accent);
            font-weight: 700;
            text-decoration: none;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div style="width:48px;height:48px;border-radius:14px;background:var(--gradient-brand);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#fff;box-shadow:var(--shadow-glow);">
                <?= icon('sparkles', 'w-6 h-6') ?>
            </div>
            <h2 style="font-weight:900;letter-spacing:-0.03em;font-size:22px;color:var(--text-main);">SocialNet</h2>
            <p style="font-size:13px;color:var(--text-muted);margin-top:4px;">Yeni Nesil Elite Sosyal Medya Platformu</p>
        </div>

        <?php if ($success = flash('success')): ?>
            <div class="sn-alert sn-alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error = flash('error')): ?>
            <div class="sn-alert sn-alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </div>
</div>
</body>
</html>
