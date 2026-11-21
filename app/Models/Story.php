<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

class Story extends Model
{
    protected string $table = 'stories';
    protected array $fillable = [
        'user_id',
        'media_path',
        'media_type',
        'caption',
        'views_count',
        'expires_at',
        'is_highlight',
        'highlight_title'
    ];

    public function createStory(int $userId, string $mediaPath, string $mediaType = 'image', ?string $caption = null): int|false
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        return (int)$this->create([
            'user_id'     => $userId,
            'media_path'  => $mediaPath,
            'media_type'  => $mediaType,
            'caption'     => $caption,
            'expires_at'  => $expiresAt
        ]);
    }

    public function getActiveStoriesGroupedByUser(?int $currentUserId = null): array
    {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT s.*, u.username, u.full_name, u.is_verified, prof.avatar as user_avatar
                FROM `stories` s
                JOIN `users` u ON u.id = s.user_id AND u.is_banned = 0
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                WHERE s.expires_at > :now AND s.is_highlight = 0
                ORDER BY s.created_at ASC";

        $stories = Database::fetchAll($sql, ['now' => $now]);

        // Group by user
        $grouped = [];
        foreach ($stories as $story) {
            $uid = $story['user_id'];
            if (!isset($grouped[$uid])) {
                $grouped[$uid] = [
                    'user_id'     => $uid,
                    'username'    => $story['username'],
                    'full_name'   => $story['full_name'],
                    'user_avatar' => $story['user_avatar'],
                    'stories'     => []
                ];
            }
            $grouped[$uid]['stories'][] = $story;
        }

        return array_values($grouped);
    }

    public function recordView(int $storyId, int $userId): void
    {
        $existing = Database::fetch("SELECT id FROM `story_views` WHERE `story_id` = :sid AND `user_id` = :uid", [
            'sid' => $storyId,
            'uid' => $userId
        ]);

        if (!$existing) {
            Database::execute("INSERT INTO `story_views` (`story_id`, `user_id`) VALUES (:sid, :uid)", [
                'sid' => $storyId,
                'uid' => $userId
            ]);
            Database::execute("UPDATE `stories` SET `views_count` = `views_count` + 1 WHERE `id` = :sid", ['sid' => $storyId]);
        }
    }
}
