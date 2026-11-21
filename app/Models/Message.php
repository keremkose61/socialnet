<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Message extends Model
{
    protected string $table = 'messages';
    protected array $fillable = [
        'conversation_id',
        'sender_id',
        'message_type',
        'body',
        'media_path',
        'is_deleted'
    ];

    public function sendMessage(int $conversationId, int $senderId, ?string $body, string $type = 'text', ?string $mediaPath = null): int|false
    {
        $msgId = (int)$this->create([
            'conversation_id' => $conversationId,
            'sender_id'       => $senderId,
            'message_type'    => $type,
            'body'            => $body,
            'media_path'      => $mediaPath
        ]);

        if ($msgId) {
            Database::execute("UPDATE `conversations` SET `last_message_id` = :mid, `updated_at` = NOW() WHERE `id` = :cid", [
                'mid' => $msgId,
                'cid' => $conversationId
            ]);
        }

        return $msgId;
    }

    public function getConversationMessages(int $conversationId, int $limit = 50): array
    {
        $sql = "SELECT m.*, u.username, u.full_name, prof.avatar as sender_avatar
                FROM `messages` m
                JOIN `users` u ON u.id = m.sender_id
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                WHERE m.conversation_id = :cid AND m.is_deleted = 0
                ORDER BY m.created_at ASC
                LIMIT {$limit}";

        return Database::fetchAll($sql, ['cid' => $conversationId]);
    }
}
