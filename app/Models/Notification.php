<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';
    protected array $fillable = [
        'user_id',
        'sender_id',
        'type',
        'target_type',
        'target_id',
        'is_read'
    ];

    public function send(int $userId, int $senderId, string $type, ?string $targetType = null, ?int $targetId = null): int|false
    {
        // Don't notify oneself
        if ($userId === $senderId) {
            return false;
        }

        // Avoid duplicate spam notifications within a short window
        $existing = Database::fetch(
            "SELECT id FROM `notifications` 
             WHERE `user_id` = :uid AND `sender_id` = :sid AND `type` = :type AND `target_id` = :tid AND `is_read` = 0",
            ['uid' => $userId, 'sid' => $senderId, 'type' => $type, 'tid' => $targetId]
        );

        if ($existing) {
            return $existing['id'];
        }

        return (int)$this->create([
            'user_id'     => $userId,
            'sender_id'   => $senderId,
            'type'        => $type,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'is_read'     => 0
        ]);
    }

    public function getUserNotifications(int $userId, int $limit = 30): array
    {
        $sql = "SELECT n.*, u.username, u.full_name, prof.avatar as sender_avatar
                FROM `notifications` n
                JOIN `users` u ON u.id = n.sender_id
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                WHERE n.user_id = :uid
                ORDER BY n.created_at DESC
                LIMIT {$limit}";

        return Database::fetchAll($sql, ['uid' => $userId]);
    }

    public function getUnreadCount(int $userId): int
    {
        $res = Database::fetch("SELECT COUNT(*) as unread FROM `notifications` WHERE `user_id` = :uid AND `is_read` = 0", ['uid' => $userId]);
        return (int)($res['unread'] ?? 0);
    }

    public function markAllAsRead(int $userId): void
    {
        Database::execute("UPDATE `notifications` SET `is_read` = 1 WHERE `user_id` = :uid AND `is_read` = 0", ['uid' => $userId]);
    }
}
