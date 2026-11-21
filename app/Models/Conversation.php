<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Conversation extends Model
{
    protected string $table = 'conversations';
    protected array $fillable = [
        'type',
        'title',
        'creator_id',
        'last_message_id'
    ];

    public function getOrCreateDirect(int $userOne, int $userTwo): int
    {
        // Check if direct conversation exists
        $sql = "SELECT c.id 
                FROM `conversations` c
                JOIN `conversation_members` cm1 ON cm1.conversation_id = c.id AND cm1.user_id = :u1
                JOIN `conversation_members` cm2 ON cm2.conversation_id = c.id AND cm2.user_id = :u2
                WHERE c.type = 'direct'
                LIMIT 1";

        $res = Database::fetch($sql, ['u1' => $userOne, 'u2' => $userTwo]);
        if ($res) {
            return (int)$res['id'];
        }

        // Create new direct conversation
        Database::beginTransaction();
        try {
            $convId = (int)$this->create([
                'type'       => 'direct',
                'creator_id' => $userOne
            ]);

            Database::execute("INSERT INTO `conversation_members` (`conversation_id`, `user_id`, `role`) VALUES (:cid, :uid, 'member')", [
                'cid' => $convId,
                'uid' => $userOne
            ]);
            Database::execute("INSERT INTO `conversation_members` (`conversation_id`, `user_id`, `role`) VALUES (:cid, :uid, 'member')", [
                'cid' => $convId,
                'uid' => $userTwo
            ]);

            Database::commit();
            return $convId;
        } catch (\Throwable $e) {
            Database::rollBack();
            error_log("Failed to create conversation: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserConversations(int $userId): array
    {
        $sql = "SELECT c.*, 
                       m.body as last_message_body, m.message_type as last_message_type, m.created_at as last_message_time,
                       other_u.id as recipient_id, other_u.username as recipient_username, other_u.full_name as recipient_name,
                       other_prof.avatar as recipient_avatar
                FROM `conversations` c
                JOIN `conversation_members` cm ON cm.conversation_id = c.id AND cm.user_id = :uid
                LEFT JOIN `messages` m ON m.id = c.last_message_id
                LEFT JOIN `conversation_members` other_cm ON other_cm.conversation_id = c.id AND other_cm.user_id != :uid2
                LEFT JOIN `users` other_u ON other_u.id = other_cm.user_id
                LEFT JOIN `user_profiles` other_prof ON other_prof.user_id = other_u.id
                ORDER BY COALESCE(m.created_at, c.created_at) DESC";

        return Database::fetchAll($sql, ['uid' => $userId, 'uid2' => $userId]);
    }
}
