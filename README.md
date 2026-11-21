# SocialNet – Profesyonel Sosyal Medya Platformu

Saf PHP (Pure PHP 8.3+) ve MVC (Model-View-Controller) mimarisi ile WAMP sunucu ortamı için sıfırdan geliştirilmiş, modern ve yüksek performanslı sosyal medya platformu.

## 🚀 Teknolojiler
- **PHP**: 8.3.28+ (Saf PHP, Framework kullanılmamıştır)
- **Sunucu**: Apache 2.4.65 (WampServer 64-bit)
- **Veritabanı**: MySQL 8.4.7 (InnoDB, UTF-8 mb4 unicode)
- **Mimari**: Modüler Özel MVC (Model-View-Controller)
- **Güvenlik**: PDO Prepared Statements, CSRF Koruması, XSS Koruması, Password Hashing (BCrypt), Güvenli Oturumlar (Session Protection)
- **Frontend**: HTML5, Modern CSS3 (Koyu Tema, Mor & Turkuaz Palet, Glassmorphism), Vanilla JavaScript, Fetch API, AJAX

## 📂 Dizin Yapısı
```text
C:\wamp64\www\socialnet\
│
├── app/
│   ├── Controllers/     # HomeController, AuthController, PostController, ProfileController, vb.
│   ├── Models/          # User, Post, Comment, Story, Message, Conversation, Notification, vb.
│   ├── Middleware/      # AuthMiddleware, CsrfMiddleware, GuestMiddleware, AdminMiddleware
│   ├── Core/            # App, Router, Controller, Model, Request, Response, Database, Session, Env, Autoloader
│   └── Helpers/         # functions.php (url, asset, upload, csrf_token, auth, e, vb.)
│
├── config/
│   ├── database.php     # PDO bağlantı ayarları
│   └── app.php          # Genel uygulama yapılandırması
│
├── public/              # Web kök dizini (Document Root)
│   ├── index.php        # Uygulama giriş noktası
│   ├── .htaccess        # URL Routing rewrite kuralları
│   ├── assets/          # CSS, JS, görseller
│   └── uploads/         # Avatarlar, kapaklar, gönderi medyaları, hikâyeler
│
├── views/
│   ├── layouts/         # main.php, auth.php, admin.php
│   ├── home/            # Ana sayfa akışı, hikâyeler, trendler
│   ├── auth/            # Giriş yap, kayıt ol
│   ├── profile/         # Kullanıcı profili, profil düzenleme
│   ├── explore/         # Arama, trendler, etiketler
│   ├── messages/        # İki panelli mesajlaşma arayüzü
│   ├── notifications/   # Bildirim merkezi
│   ├── admin/           # Yönetici paneli ve istatistikler
│   └── errors/          # 404 sayfası
│
├── routes/
│   └── web.php          # Tüm web yönlendirmeleri
│
├── database/
│   └── socialnet.sql    # 28 tablolu ilişkisel şema ve varsayılan veriler
│
├── storage/
│   ├── logs/            # Uygulama hata kayıtları
│   └── cache/           # Önbellek dizini
│
├── .env                 # Ortam değişkenleri
└── .htaccess            # public/ dizinine otomatik yönlendirme
```

## 🌐 Erişim Adresleri (Local)
- **Ana Sayfa / Akış**: `http://localhost/socialnet/public/`
- **Giriş Yap**: `http://localhost/socialnet/public/login`
- **Kayıt Ol**: `http://localhost/socialnet/public/register`
- **Keşfet & Arama**: `http://localhost/socialnet/public/explore`
- **Mesajlar**: `http://localhost/socialnet/public/messages`
- **Bildirimler**: `http://localhost/socialnet/public/notifications`
- **Yönetici Paneli**: `http://localhost/socialnet/public/admin`

*(Not: VirtualHost yapılandırması yapıldığında doğrudan `http://socialnet.test` olarak da çalışır.)*

## 🔑 Varsayılan Giriş Bilgileri
- **Yönetici Kullanıcı Adı**: `admin`
- **Şifre**: `Admin12345!`
- **E-posta**: `admin@socialnet.test`

- **Test Kullanıcısı**: `deneme`
- **Şifre**: `123456`
