<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Comment extends Model
{
    protected string $table = 'comments';
    protected array $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'likes_count'
    ];

    public function addComment(int $postId, int $userId, string $content, ?int $parentId = null): int|false
    {
        $commentId = (int)$this->create([
            'post_id'   => $postId,
            'user_id'   => $userId,
            'parent_id' => $parentId,
            'content'   => $content
        ]);

        if ($commentId) {
            Database::execute("UPDATE `posts` SET `comments_count` = `comments_count` + 1 WHERE `id` = :pid", ['pid' => $postId]);

            // Notify post owner or parent comment owner
            $post = (new Post())->find($postId);
            if ($parentId) {
                $parentComment = $this->find($parentId);
                if ($parentComment && (int)$parentComment['user_id'] !== $userId) {
                    (new Notification())->send(
                        userId: (int)$parentComment['user_id'],
                        senderId: $userId,
                        type: 'reply_comment',
                        targetType: 'comment',
                        targetId: $commentId
                    );
                }
            } elseif ($post && (int)$post['user_id'] !== $userId) {
                (new Notification())->send(
                    userId: (int)$post['user_id'],
                    senderId: $userId,
                    type: 'comment_post',
                    targetType: 'post',
                    targetId: $postId
                );
            }
        }

        return $commentId;
    }

    public function getPostComments(int $postId, ?int $currentUserId = null): array
    {
        $sql = "SELECT c.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar
                FROM `comments` c
                JOIN `users` u ON u.id = c.user_id AND u.is_banned = 0
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                WHERE c.post_id = :pid AND c.parent_id IS NULL
                ORDER BY c.created_at ASC";

        $comments = Database::fetchAll($sql, ['pid' => $postId]);

        // Get replies
        foreach ($comments as &$com) {
            $repSql = "SELECT c.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar
                       FROM `comments` c
                       JOIN `users` u ON u.id = c.user_id AND u.is_banned = 0
                       LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                       WHERE c.parent_id = :parent_id
                       ORDER BY c.created_at ASC";
            $com['replies'] = Database::fetchAll($repSql, ['parent_id' => $com['id']]);
        }

        return $comments;
    }
}
