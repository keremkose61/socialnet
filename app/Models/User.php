<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'username',
        'email',
        'password_hash',
        'full_name',
        'role_id',
        'is_private',
        'is_verified',
        'is_banned',
        'ban_reason',
        'email_verified_at',
        'verification_token',
        'reset_token',
        'reset_token_expires_at',
        'remember_token',
        'last_active_at'
    ];

    public function findByUsername(string $username): ?array
    {
        return $this->firstWhere('username', $username);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->firstWhere('email', $email);
    }

    public function findByLogin(string $login): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `username` = :login_user OR `email` = :login_email LIMIT 1";
        return Database::fetch($sql, ['login_user' => $login, 'login_email' => $login]);
    }

    public function findWithProfile(int $id): ?array
    {
        $sql = "SELECT u.*, 
                       p.bio, p.website, p.avatar, p.cover_image, p.location, p.birthdate, p.gender,
                       s.theme, s.notification_email, s.notification_likes, s.notification_comments, 
                       s.notification_messages, s.allow_tagging, s.show_activity_status,
                       r.name as role_name, r.slug as role_slug
                FROM `users` u
                LEFT JOIN `user_profiles` p ON p.user_id = u.id
                LEFT JOIN `user_settings` s ON s.user_id = u.id
                LEFT JOIN `roles` r ON r.id = u.role_id
                WHERE u.id = :id
                LIMIT 1";
        return Database::fetch($sql, ['id' => $id]);
    }

    public function findByUsernameWithProfile(string $username): ?array
    {
        $sql = "SELECT u.*, 
                       p.bio, p.website, p.avatar, p.cover_image, p.location, p.birthdate, p.gender,
                       s.theme, s.notification_email, s.notification_likes, s.notification_comments, 
                       s.notification_messages, s.allow_tagging, s.show_activity_status,
                       r.name as role_name, r.slug as role_slug
                FROM `users` u
                LEFT JOIN `user_profiles` p ON p.user_id = u.id
                LEFT JOIN `user_settings` s ON s.user_id = u.id
                LEFT JOIN `roles` r ON r.id = u.role_id
                WHERE u.username = :username
                LIMIT 1";
        return Database::fetch($sql, ['username' => $username]);
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function register(array $userData, array $profileData = []): int|false
    {
        Database::beginTransaction();
        try {
            $userData['password_hash'] = $this->hashPassword($userData['password']);
            unset($userData['password']);

            if (empty($userData['role_id'])) {
                $userData['role_id'] = 3; // Standard user
            }

            $userId = (int)$this->create($userData);
            if (!$userId) {
                Database::rollBack();
                return false;
            }

            // Create profile
            $profileSql = "INSERT INTO `user_profiles` (`user_id`, `bio`, `website`, `avatar`, `cover_image`, `location`)
                           VALUES (:user_id, :bio, :website, :avatar, :cover_image, :location)";
            Database::insert($profileSql, [
                'user_id'     => $userId,
                'bio'         => $profileData['bio'] ?? null,
                'website'     => $profileData['website'] ?? null,
                'avatar'      => $profileData['avatar'] ?? 'default-avatar.png',
                'cover_image' => $profileData['cover_image'] ?? 'default-cover.jpg',
                'location'    => $profileData['location'] ?? null,
            ]);

            // Create settings
            $settingsSql = "INSERT INTO `user_settings` (`user_id`, `theme`) VALUES (:user_id, 'dark')";
            Database::insert($settingsSql, ['user_id' => $userId]);

            Database::commit();
            return $userId;
        } catch (\Throwable $e) {
            Database::rollBack();
            error_log("Registration failed: " . $e->getMessage());
            return false;
        }
    }

    public function getCounts(int $userId): array
    {
        $postsSql = "SELECT COUNT(*) as total FROM `posts` WHERE `user_id` = :uid";
        $postsCount = Database::fetch($postsSql, ['uid' => $userId])['total'] ?? 0;

        $followersSql = "SELECT COUNT(*) as total FROM `followers` WHERE `following_id` = :uid";
        $followersCount = Database::fetch($followersSql, ['uid' => $userId])['total'] ?? 0;

        $followingSql = "SELECT COUNT(*) as total FROM `followers` WHERE `follower_id` = :uid";
        $followingCount = Database::fetch($followingSql, ['uid' => $userId])['total'] ?? 0;

        return [
            'posts'     => (int)$postsCount,
            'followers' => (int)$followersCount,
            'following' => (int)$followingCount,
        ];
    }

    public function isFollowing(int $followerId, int $followingId): bool
    {
        $sql = "SELECT id FROM `followers` WHERE `follower_id` = :follower AND `following_id` = :following LIMIT 1";
        return (bool)Database::fetch($sql, ['follower' => $followerId, 'following' => $followingId]);
    }

    public function search(string $query, int $limit = 10): array
    {
        $sql = "SELECT u.id, u.username, u.full_name, u.is_verified, p.avatar, p.bio
                FROM `users` u
                LEFT JOIN `user_profiles` p ON p.user_id = u.id
                WHERE (u.username LIKE :q_user OR u.full_name LIKE :q_name) AND u.is_banned = 0
                LIMIT {$limit}";
        $like = "%{$query}%";
        return Database::fetchAll($sql, ['q_user' => $like, 'q_name' => $like]);
    }
}
