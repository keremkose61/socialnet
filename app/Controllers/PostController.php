<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\Comment;
use App\Models\Post;

class PostController extends Controller
{
    private Post $postModel;

    public function __construct()
    {
        $this->postModel = new Post();
    }

    public function create(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $content = trim($this->request->input('content', ''));
        $visibility = in_array($this->request->input('visibility'), ['public', 'followers', 'private'])
            ? $this->request->input('visibility')
            : 'public';

        $mediaFiles = [];

        // Handle uploaded media files
        if (!empty($_FILES['media']['name'][0])) {
            $allowedImages = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $allowedVideos = ['video/mp4', 'video/webm', 'video/quicktime'];
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/posts/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $count = count($_FILES['media']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['media']['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $tmpName = $_FILES['media']['tmp_name'][$i];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmpName);
                finfo_close($finfo);

                $isImg = in_array($mime, $allowedImages);
                $isVid = in_array($mime, $allowedVideos);

                if (!$isImg && !$isVid) {
                    continue; // Skip unsupported files
                }

                $ext = pathinfo($_FILES['media']['name'][$i], PATHINFO_EXTENSION);
                $newFilename = 'post_' . uniqid() . '_' . time() . '.' . $ext;
                $target = $uploadDir . $newFilename;

                if (move_uploaded_file($tmpName, $target)) {
                    $mediaFiles[] = [
                        'path' => 'posts/' . $newFilename,
                        'type' => $isVid ? 'video' : 'image',
                        'mime' => $mime,
                        'size' => $_FILES['media']['size'][$i]
                    ];
                }
            }
        }

        if (empty($content) && empty($mediaFiles)) {
            if ($this->request->isAjax()) {
                $this->json(['success' => false, 'message' => 'Lütfen bir metin girin veya medya yükleyin.'], 422);
            }
            Session::setFlash('error', 'Boş gönderi paylaşılamaz.');
            $this->redirect(url('/'));
            return;
        }

        $postId = $this->postModel->createPost($user['id'], $content, $mediaFiles, $visibility);

        if ($this->request->isAjax()) {
            $post = $this->postModel->findWithDetails($postId, $user['id']);
            $this->json([
                'success' => true,
                'message' => 'Gönderiniz başarıyla paylaşıldı!',
                'post'    => $post
            ]);
            return;
        }

        Session::setFlash('success', 'Gönderiniz başarıyla paylaşıldı!');
        $this->redirect(url('/'));
    }

    public function like(int $id): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $res = $this->postModel->toggleLike($id, $user['id']);
        $this->json([
            'success'     => true,
            'liked'       => $res['liked'],
            'likes_count' => $res['likes_count']
        ]);
    }

    public function comment(int $id): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $content = trim($this->request->input('content', ''));
        $parentId = $this->request->input('parent_id') ? (int)$this->request->input('parent_id') : null;

        if (empty($content)) {
            $this->json(['success' => false, 'message' => 'Yorum metni boş olamaz.'], 422);
            return;
        }

        $commentModel = new Comment();
        $commentId = $commentModel->addComment($id, $user['id'], $content, $parentId);

        if ($commentId) {
            $this->json([
                'success' => true,
                'comment' => [
                    'id'          => $commentId,
                    'user_id'     => $user['id'],
                    'username'    => $user['username'],
                    'full_name'   => $user['full_name'],
                    'avatar'      => upload($user['avatar'] ?? 'default-avatar.png'),
                    'content'     => e($content),
                    'created_at'  => 'Az önce'
                ]
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Yorum eklenirken hata oluştu.'], 500);
        }
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $post = $this->postModel->find($id);
        if (!$post) {
            $this->json(['success' => false, 'message' => 'Gönderi bulunamadı.'], 404);
            return;
        }

        // Check ownership or admin
        if ((int)$post['user_id'] !== $user['id'] && (int)$user['role_id'] !== 1) {
            $this->json(['success' => false, 'message' => 'Bu gönderiyi silme yetkiniz yok.'], 403);
            return;
        }

        $this->postModel->delete($id);

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'message' => 'Gönderi silindi.']);
            return;
        }

        Session::setFlash('success', 'Gönderi silindi.');
        $this->redirect(url('/'));
    }

    public function save(int $id): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $existing = Database::fetch("SELECT id FROM `saved_posts` WHERE `user_id` = :uid AND `post_id` = :pid", [
            'uid' => $user['id'],
            'pid' => $id
        ]);

        if ($existing) {
            Database::execute("DELETE FROM `saved_posts` WHERE `id` = :id", ['id' => $existing['id']]);
            $saved = false;
        } else {
            Database::execute("INSERT INTO `saved_posts` (`user_id`, `post_id`) VALUES (:uid, :pid)", [
                'uid' => $user['id'],
                'pid' => $id
            ]);
            $saved = true;
        }

        $this->json(['success' => true, 'saved' => $saved]);
    }

    public function saved(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $sql = "SELECT p.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar,
                       1 as user_has_saved
                FROM `saved_posts` sp
                JOIN `posts` p ON p.id = sp.post_id
                JOIN `users` u ON u.id = p.user_id
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                WHERE sp.user_id = :uid
                ORDER BY sp.created_at DESC";

        $posts = Database::fetchAll($sql, ['uid' => $user['id']]);

        // Attach media
        if (!empty($posts)) {
            $postIds = array_column($posts, 'id');
            $placeholders = implode(',', array_fill(0, count($postIds), '?'));
            $mediaRows = Database::fetchAll("SELECT * FROM `post_media` WHERE `post_id` IN ({$placeholders}) ORDER BY `sort_order` ASC", $postIds);
            $mediaByPost = [];
            foreach ($mediaRows as $m) {
                $mediaByPost[$m['post_id']][] = $m;
            }
            foreach ($posts as &$p) {
                $p['media'] = $mediaByPost[$p['id']] ?? [];
            }
        }

        $this->render('posts/saved', [
            'pageTitle'   => 'Kaydedilenler – SocialNet',
            'currentUser' => $user,
            'posts'       => $posts
        ]);
    }

    public function show(int $id): void
    {
        $currentUserId = \App\Core\Session::getUserId();
        $sql = "SELECT p.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar,
                       (SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id) as likes_count,
                       (SELECT COUNT(*) FROM `comments` WHERE `post_id` = p.id) as comments_count,
                       " . ($currentUserId ? "(SELECT COUNT(*) FROM `post_likes` WHERE `post_id` = p.id AND `user_id` = {$currentUserId})" : "0") . " as user_has_liked,
                       " . ($currentUserId ? "(SELECT COUNT(*) FROM `saved_posts` WHERE `post_id` = p.id AND `user_id` = {$currentUserId})" : "0") . " as user_has_saved
                FROM `posts` p
                JOIN `users` u ON u.id = p.user_id
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                WHERE p.id = :id";
        $post = Database::fetch($sql, ['id' => $id]);
        if (!$post) {
            $this->redirect(url('/'));
            return;
        }

        $post['media'] = Database::fetchAll("SELECT * FROM `post_media` WHERE `post_id` = :pid ORDER BY `sort_order` ASC", ['pid' => $id]);
        $post['comments'] = Database::fetchAll("SELECT c.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar
                                                FROM `comments` c
                                                JOIN `users` u ON u.id = c.user_id
                                                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                                                WHERE c.post_id = :pid
                                                ORDER BY c.created_at ASC", ['pid' => $id]);

        $this->render('posts/show', [
            'pageTitle' => e($post['full_name']) . ' Gönderisi – SocialNet',
            'post'      => $post
        ]);
    }
}
