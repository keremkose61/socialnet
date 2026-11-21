<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Post;
use App\Models\User;

class ExploreController extends Controller
{
    public function index(): void
    {
        $user = $this->currentUser();
        $userId = $user['id'] ?? null;

        $q = trim($this->request->input('q', ''));
        $postModel = new Post();
        $userModel = new User();

        $searchResults = [];
        if (!empty($q)) {
            $searchResults['users'] = $userModel->search($q, 10);
            // Search posts by hashtag or content
            $searchResults['posts'] = Database::fetchAll(
                "SELECT p.*, u.username, u.full_name, prof.avatar as user_avatar
                 FROM `posts` p
                 JOIN `users` u ON u.id = p.user_id AND u.is_banned = 0
                 LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                 WHERE p.visibility = 'public' AND p.content LIKE :q
                 ORDER BY p.created_at DESC LIMIT 15",
                ['q' => "%{$q}%"]
            );
        }

        $trendingPosts = $postModel->getExplore($userId, page: 1, perPage: 18);
        $trendingHashtags = Database::fetchAll("SELECT * FROM `hashtags` ORDER BY `posts_count` DESC LIMIT 10");

        $this->render('explore/index', [
            'pageTitle'        => 'Keşfet - SocialNet',
            'currentUser'      => $user,
            'trendingPosts'    => $trendingPosts,
            'trendingHashtags' => $trendingHashtags,
            'query'            => $q,
            'searchResults'    => $searchResults
        ]);
    }
}
