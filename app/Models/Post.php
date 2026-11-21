<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Post extends Model
{
    protected string $table = 'posts';
    protected array $fillable = [
        'user_id',
        'content',
        'type',
        'visibility',
        'location',
        'likes_count',
        'comments_count',
        'shares_count',
        'views_count',
        'is_pinned'
    ];

    public function createPost(int $userId, string $content, array $mediaFiles = [], string $visibility = 'public'): int|false
    {
        Database::beginTransaction();
        try {
            $type = 'text';
            if (!empty($mediaFiles)) {
                $type = count($mediaFiles) > 1 ? 'carousel' : ($mediaFiles[0]['type'] ?? 'image');
            }

            $postId = (int)$this->create([
                'user_id'    => $userId,
                'content'    => $content,
                'type'       => $type,
                'visibility' => $visibility
            ]);

            if (!$postId) {
                Database::rollBack();
                return false;
            }

            // Insert media files
            if (!empty($mediaFiles)) {
                $mediaSql = "INSERT INTO `post_media` (`post_id`, `file_path`, `file_type`, `mime_type`, `file_size`, `sort_order`)
                             VALUES (:post_id, :file_path, :file_type, :mime_type, :file_size, :sort_order)";
                foreach ($mediaFiles as $idx => $m) {
                    Database::insert($mediaSql, [
                        'post_id'    => $postId,
                        'file_path'  => $m['path'],
                        'file_type'  => $m['type'],
                        'mime_type'  => $m['mime'],
                        'file_size'  => $m['size'],
                        'sort_order' => $idx
                    ]);
                }
            }

            // Extract and sync hashtags
            $this->syncHashtags($postId, $content);

            Database::commit();
            return $postId;
        } catch (\Throwable $e) {
            Database::rollBack();
            error_log("Post creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function syncHashtags(int $postId, string $content): void
    {
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $content, $matches);
        if (empty($matches[1])) {
            return;
        }

        $tags = array_unique(array_map('mb_strtolower', $matches[1]));
        foreach ($tags as $tag) {
            // Find or create hashtag
            $tagRow = Database::fetch("SELECT id FROM `hashtags` WHERE `tag` = :tag", ['tag' => $tag]);
            if ($tagRow) {
                $hashtagId = $tagRow['id'];
                Database::execute("UPDATE `hashtags` SET `posts_count` = `posts_count` + 1 WHERE `id` = :id", ['id' => $hashtagId]);
            } else {
                $hashtagId = Database::insert("INSERT INTO `hashtags` (`tag`, `posts_count`) VALUES (:tag, 1)", ['tag' => $tag]);
            }

            if ($hashtagId) {
                Database::execute("INSERT IGNORE INTO `post_hashtags` (`post_id`, `hashtag_id`) VALUES (:pid, :hid)", [
                    'pid' => $postId,
                    'hid' => $hashtagId
                ]);
            }
        }
    }

    public function getFeed(?int $currentUserId, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $likeJoin = "";
        $likeSelect = "0 as user_has_liked, 0 as user_has_saved";
        $where = "WHERE p.visibility = 'public'";
        $params = [];

        if ($currentUserId) {
            $likeSelect = "IF(pl.id IS NOT NULL, 1, 0) as user_has_liked, IF(sp.id IS NOT NULL, 1, 0) as user_has_saved";
            $likeJoin = "LEFT JOIN `post_likes` pl ON pl.post_id = p.id AND pl.user_id = :current_user_id
                         LEFT JOIN `saved_posts` sp ON sp.post_id = p.id AND sp.user_id = :current_user_id2";
            $where = "WHERE (p.visibility = 'public' 
                      OR p.user_id = :uid_self 
                      OR (p.visibility = 'followers' AND p.user_id IN (SELECT following_id FROM `followers` WHERE follower_id = :uid_following)))";
            $params = [
                'current_user_id'  => $currentUserId,
                'current_user_id2' => $currentUserId,
                'uid_self'         => $currentUserId,
                'uid_following'    => $currentUserId,
            ];
        }

        $sql = "SELECT p.*, 
                       u.username, u.full_name, u.is_verified, 
                       prof.avatar as user_avatar,
                       {$likeSelect}
                FROM `posts` p
                JOIN `users` u ON u.id = p.user_id AND u.is_banned = 0
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                {$likeJoin}
                {$where}
                ORDER BY p.is_pinned DESC, p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $posts = Database::fetchAll($sql, $params);

        // Fetch media for posts
        return $this->attachMedia($posts);
    }

    public function getExplore(?int $currentUserId, int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $likeSelect = "0 as user_has_liked";
        $likeJoin = "";
        $params = [];

        if ($currentUserId) {
            $likeSelect = "IF(pl.id IS NOT NULL, 1, 0) as user_has_liked";
            $likeJoin = "LEFT JOIN `post_likes` pl ON pl.post_id = p.id AND pl.user_id = :cuid";
            $params['cuid'] = $currentUserId;
        }

        $sql = "SELECT p.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar, {$likeSelect}
                FROM `posts` p
                JOIN `users` u ON u.id = p.user_id AND u.is_banned = 0 AND u.is_private = 0
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                {$likeJoin}
                WHERE p.visibility = 'public'
                ORDER BY (p.likes_count * 2 + p.comments_count * 3) DESC, p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $posts = Database::fetchAll($sql, $params);
        return $this->attachMedia($posts);
    }

    public function getUserPosts(int $targetUserId, ?int $currentUserId = null, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $likeSelect = "0 as user_has_liked";
        $likeJoin = "";
        $params = ['target_uid' => $targetUserId];

        if ($currentUserId) {
            $likeSelect = "IF(pl.id IS NOT NULL, 1, 0) as user_has_liked";
            $likeJoin = "LEFT JOIN `post_likes` pl ON pl.post_id = p.id AND pl.user_id = :cuid";
            $params['cuid'] = $currentUserId;
        }

        $visibilityCondition = "AND p.visibility = 'public'";
        if ($currentUserId === $targetUserId) {
            $visibilityCondition = "";
        }

        $sql = "SELECT p.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar, {$likeSelect}
                FROM `posts` p
                JOIN `users` u ON u.id = p.user_id
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                {$likeJoin}
                WHERE p.user_id = :target_uid {$visibilityCondition}
                ORDER BY p.is_pinned DESC, p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $posts = Database::fetchAll($sql, $params);
        return $this->attachMedia($posts);
    }

    public function findWithDetails(int $postId, ?int $currentUserId = null): ?array
    {
        $likeSelect = "0 as user_has_liked, 0 as user_has_saved";
        $likeJoin = "";
        $params = ['pid' => $postId];

        if ($currentUserId) {
            $likeSelect = "IF(pl.id IS NOT NULL, 1, 0) as user_has_liked, IF(sp.id IS NOT NULL, 1, 0) as user_has_saved";
            $likeJoin = "LEFT JOIN `post_likes` pl ON pl.post_id = p.id AND pl.user_id = :cuid
                         LEFT JOIN `saved_posts` sp ON sp.post_id = p.id AND sp.user_id = :cuid2";
            $params['cuid'] = $currentUserId;
            $params['cuid2'] = $currentUserId;
        }

        $sql = "SELECT p.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar, {$likeSelect}
                FROM `posts` p
                JOIN `users` u ON u.id = p.user_id
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                {$likeJoin}
                WHERE p.id = :pid
                LIMIT 1";

        $post = Database::fetch($sql, $params);
        if (!$post) {
            return null;
        }

        $media = Database::fetchAll("SELECT * FROM `post_media` WHERE `post_id` = :pid ORDER BY `sort_order` ASC", ['pid' => $postId]);
        $post['media'] = $media;

        return $post;
    }

    public function toggleLike(int $postId, int $userId): array
    {
        $post = $this->find($postId);
        if (!$post) {
            return ['liked' => false, 'likes_count' => 0];
        }

        $existing = Database::fetch("SELECT id FROM `post_likes` WHERE `post_id` = :pid AND `user_id` = :uid", [
            'pid' => $postId,
            'uid' => $userId
        ]);

        if ($existing) {
            Database::execute("DELETE FROM `post_likes` WHERE `id` = :id", ['id' => $existing['id']]);
            Database::execute("UPDATE `posts` SET `likes_count` = GREATEST(0, CAST(`likes_count` AS SIGNED) - 1) WHERE `id` = :pid", ['pid' => $postId]);
            $liked = false;
        } else {
            Database::execute("INSERT INTO `post_likes` (`post_id`, `user_id`) VALUES (:pid, :uid)", [
                'pid' => $postId,
                'uid' => $userId
            ]);
            Database::execute("UPDATE `posts` SET `likes_count` = `likes_count` + 1 WHERE `id` = :pid", ['pid' => $postId]);
            $liked = true;

            // Trigger notification
            $post = $this->find($postId);
            if ($post && (int)$post['user_id'] !== $userId) {
                $notifModel = new Notification();
                $notifModel->send(
                    userId: (int)$post['user_id'],
                    senderId: $userId,
                    type: 'like_post',
                    targetType: 'post',
                    targetId: $postId
                );
            }
        }

        $likesCount = Database::fetch("SELECT likes_count FROM `posts` WHERE `id` = :pid", ['pid' => $postId])['likes_count'] ?? 0;

        return ['liked' => $liked, 'likes_count' => (int)$likesCount];
    }

    private function attachMedia(array $posts): array
    {
        if (empty($posts)) {
            return [];
        }

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

        return $posts;
    }
}
