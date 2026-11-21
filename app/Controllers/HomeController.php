<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\Post;
use App\Models\Story;
use App\Models\User;

class HomeController extends Controller
{
    public function index(): void
    {
        $user = $this->currentUser();
        $userId = $user['id'] ?? null;

        $postModel = new Post();
        $feedPosts = $postModel->getFeed($userId, page: 1, perPage: 15);

        $storyModel = new Story();
        $activeStories = $storyModel->getActiveStoriesGroupedByUser($userId);

        // Suggested users
        $suggestedSql = "SELECT u.id, u.username, u.full_name, u.is_verified, prof.avatar
                         FROM `users` u
                         LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                         WHERE u.is_banned = 0 " . ($userId ? "AND u.id != :uid AND u.id NOT IN (SELECT following_id FROM `followers` WHERE follower_id = :uid2)" : "") . "
                         ORDER BY RAND() LIMIT 5";
        $suggestedParams = $userId ? ['uid' => $userId, 'uid2' => $userId] : [];
        $suggestedUsers = Database::fetchAll($suggestedSql, $suggestedParams);

        // Trending hashtags
        $trendingHashtags = Database::fetchAll("SELECT * FROM `hashtags` ORDER BY `posts_count` DESC LIMIT 6");

        $this->render('home/index', [
            'pageTitle'        => 'Ana Sayfa - SocialNet',
            'currentUser'      => $user,
            'posts'            => $feedPosts,
            'stories'          => $activeStories,
            'suggestedUsers'   => $suggestedUsers,
            'trendingHashtags' => $trendingHashtags
        ]);
    }

    public function live(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $streams = Database::fetchAll("SELECT ls.*, u.username, u.full_name, prof.avatar
                                       FROM `live_streams` ls
                                       JOIN `users` u ON u.id = ls.user_id
                                       LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                                       WHERE ls.status = 'active'
                                       ORDER BY ls.started_at DESC");

        $this->render('home/live', [
            'pageTitle'   => 'Canlı Yayın – SocialNet',
            'currentUser' => $user,
            'streams'     => $streams
        ]);
    }
}
