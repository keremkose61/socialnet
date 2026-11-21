<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;

class ProfileController extends Controller
{
    private User $userModel;
    private Post $postModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->postModel = new Post();
    }

    public function show(string $username): void
    {
        $targetUser = $this->userModel->findByUsernameWithProfile($username);
        if (!$targetUser) {
            $this->render('errors/404', ['pageTitle' => 'Kullanıcı Bulunamadı']);
            return;
        }

        $currentUser = $this->currentUser();
        $currentUserId = $currentUser['id'] ?? null;
        $targetUserId = (int)$targetUser['id'];

        $counts = $this->userModel->getCounts($targetUserId);
        $isFollowing = $currentUserId ? $this->userModel->isFollowing($currentUserId, $targetUserId) : false;
        $isSelf = $currentUserId === $targetUserId;

        $posts = $this->postModel->getUserPosts($targetUserId, $currentUserId, page: 1, perPage: 12);

        $this->render('profile/show', [
            'pageTitle'    => "{$targetUser['full_name']} (@{$targetUser['username']}) - SocialNet",
            'profileUser'  => $targetUser,
            'currentUser'  => $currentUser,
            'counts'       => $counts,
            'isFollowing'  => $isFollowing,
            'isSelf'       => $isSelf,
            'posts'        => $posts
        ]);
    }

    public function edit(): void
    {
        $this->requireAuth();
        $currentUser = $this->currentUser();

        if ($this->request->isPost()) {
            $fullName = trim($this->request->input('full_name', ''));
            $bio = trim($this->request->input('bio', ''));
            $website = trim($this->request->input('website', ''));
            $location = trim($this->request->input('location', ''));

            $updateData = [];
            if (!empty($fullName)) {
                $this->userModel->update($currentUser['id'], ['full_name' => $fullName]);
            }

            // Avatar Upload
            if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
                finfo_close($finfo);

                if (in_array($mime, $allowed)) {
                    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $filename = 'avatar_' . $currentUser['id'] . '_' . time() . '.' . $ext;
                    $target = dirname(__DIR__, 2) . '/public/uploads/avatars/' . $filename;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                        $updateData['avatar'] = 'avatars/' . $filename;
                    }
                }
            }

            // Cover Upload
            if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['cover_image']['tmp_name']);
                finfo_close($finfo);

                if (in_array($mime, $allowed)) {
                    $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'cover_' . $currentUser['id'] . '_' . time() . '.' . $ext;
                    $target = dirname(__DIR__, 2) . '/public/uploads/covers/' . $filename;
                    if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target)) {
                        $updateData['cover_image'] = 'covers/' . $filename;
                    }
                }
            }

            $updateData['bio'] = $bio;
            $updateData['website'] = $website;
            $updateData['location'] = $location;

            // Ensure user_profiles row exists
            $existingProfile = Database::fetch("SELECT id FROM `user_profiles` WHERE `user_id` = :uid", ['uid' => $currentUser['id']]);
            if (!$existingProfile) {
                Database::execute("INSERT INTO `user_profiles` (`user_id`) VALUES (:uid)", ['uid' => $currentUser['id']]);
            }

            // Update user_profiles
            $setParts = [];
            $params = ['uid' => $currentUser['id']];
            foreach ($updateData as $col => $val) {
                $setParts[] = "`{$col}` = :{$col}";
                $params[$col] = $val;
            }

            Database::execute("UPDATE `user_profiles` SET " . implode(', ', $setParts) . " WHERE `user_id` = :uid", $params);

            Session::setFlash('success', 'Profil bilgileriniz güncellendi.');
            $this->redirect(url('/u/' . $currentUser['username']));
            return;
        }

        $this->render('profile/edit', [
            'pageTitle'   => 'Profili Düzenle - SocialNet',
            'currentUser' => $currentUser
        ]);
    }

    public function follow(string $username): void
    {
        $this->requireAuth();
        $currentUser = $this->currentUser();
        $targetUser = $this->userModel->findByUsername($username);

        if (!$targetUser || (int)$targetUser['id'] === $currentUser['id']) {
            $this->json(['success' => false, 'message' => 'Geçersiz takip isteği.'], 400);
            return;
        }

        $targetId = (int)$targetUser['id'];
        $followerId = $currentUser['id'];

        $existing = Database::fetch("SELECT id FROM `followers` WHERE `follower_id` = :f AND `following_id` = :t", [
            'f' => $followerId,
            't' => $targetId
        ]);

        if ($existing) {
            Database::execute("DELETE FROM `followers` WHERE `id` = :id", ['id' => $existing['id']]);
            $isFollowing = false;
        } else {
            Database::execute("INSERT INTO `followers` (`follower_id`, `following_id`) VALUES (:f, :t)", [
                'f' => $followerId,
                't' => $targetId
            ]);
            $isFollowing = true;

            // Send notification
            (new Notification())->send(
                userId: $targetId,
                senderId: $followerId,
                type: 'follow',
                targetType: 'user',
                targetId: $followerId
            );
        }

        $followersCount = Database::fetch("SELECT COUNT(*) as total FROM `followers` WHERE `following_id` = :tid", ['tid' => $targetId])['total'] ?? 0;

        $this->json([
            'success'         => true,
            'is_following'    => $isFollowing,
            'followers_count' => (int)$followersCount
        ]);
    }

    public function settings(): void
    {
        $this->requireAuth();
        $currentUser = $this->currentUser();

        $this->render('settings/index', [
            'pageTitle'   => 'Ayarlar – SocialNet',
            'currentUser' => $currentUser
        ]);
    }

    public function updateSettings(): void
    {
        $this->requireAuth();
        $currentUser = $this->currentUser();
        $userId = $currentUser['id'];

        $tab = $this->request->input('tab', 'profile');

        if ($tab === 'profile') {
            $fullName = trim($this->request->input('full_name', ''));
            $bio = trim($this->request->input('bio', ''));
            $website = trim($this->request->input('website', ''));
            $location = trim($this->request->input('location', ''));

            if (!empty($fullName)) {
                $this->userModel->update($userId, ['full_name' => $fullName]);
            }

            $updateData = ['bio' => $bio, 'website' => $website, 'location' => $location];

            // Avatar Upload
            if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
                finfo_close($finfo);

                if (in_array($mime, $allowed)) {
                    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                    $target = dirname(__DIR__, 2) . '/public/uploads/avatars/' . $filename;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                        $updateData['avatar'] = 'avatars/' . $filename;
                    }
                }
            }

            // Cover Upload
            if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['cover_image']['tmp_name']);
                finfo_close($finfo);

                if (in_array($mime, $allowed)) {
                    $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'cover_' . $userId . '_' . time() . '.' . $ext;
                    $target = dirname(__DIR__, 2) . '/public/uploads/covers/' . $filename;
                    if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target)) {
                        $updateData['cover_image'] = 'covers/' . $filename;
                    }
                }
            }

            // Ensure profile exists
            $existing = Database::fetch("SELECT id FROM `user_profiles` WHERE `user_id` = :uid", ['uid' => $userId]);
            if (!$existing) {
                Database::execute("INSERT INTO `user_profiles` (`user_id`) VALUES (:uid)", ['uid' => $userId]);
            }

            $setParts = [];
            $params = ['uid' => $userId];
            foreach ($updateData as $col => $val) {
                $setParts[] = "`{$col}` = :{$col}";
                $params[$col] = $val;
            }
            Database::execute("UPDATE `user_profiles` SET " . implode(', ', $setParts) . " WHERE `user_id` = :uid", $params);

            Session::setFlash('success', 'Profil bilgileriniz güncellendi.');
        } elseif ($tab === 'appearance') {
            $theme = in_array($this->request->input('theme'), ['dark', 'light']) ? $this->request->input('theme') : 'dark';
            Database::execute("UPDATE `user_settings` SET `theme` = :t WHERE `user_id` = :uid", ['t' => $theme, 'uid' => $userId]);
            Session::setFlash('success', 'Görünüm ve tema ayarlarınız kaydedildi.');
        } elseif ($tab === 'security') {
            $currentPassword = (string)$this->request->input('current_password', '');
            $newPassword = (string)$this->request->input('new_password', '');
            $confirmPassword = (string)$this->request->input('confirm_password', '');

            $user = $this->userModel->find($userId);
            if (!$this->userModel->verifyPassword($currentPassword, $user['password_hash'])) {
                Session::setFlash('error', 'Mevcut şifrenizi hatalı girdiniz.');
            } elseif (strlen($newPassword) < 6) {
                Session::setFlash('error', 'Yeni şifre en az 6 karakter olmalıdır.');
            } elseif ($newPassword !== $confirmPassword) {
                Session::setFlash('error', 'Yeni şifreler birbiriyle eşleşmiyor.');
            } else {
                $hash = $this->userModel->hashPassword($newPassword);
                $this->userModel->update($userId, ['password_hash' => $hash]);
                Session::setFlash('success', 'Şifreniz başarıyla değiştirildi.');
            }
        }

        $this->redirect(url('/settings'));
    }
}
