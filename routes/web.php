<?php

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\PostController;
use App\Controllers\ProfileController;
use App\Controllers\ExploreController;
use App\Controllers\StoryController;
use App\Controllers\MessageController;
use App\Controllers\NotificationController;
use App\Controllers\AdminController;

// Home & Feed
Router::get('/', [HomeController::class, 'index']);
Router::get('/live', [HomeController::class, 'live'], ['AuthMiddleware']);

// Authentication
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'processLogin'], ['CsrfMiddleware']);
Router::get('/register', [AuthController::class, 'showRegister']);
Router::post('/register', [AuthController::class, 'processRegister'], ['CsrfMiddleware']);
Router::get('/logout', [AuthController::class, 'logout']);

// Settings & Preferences
Router::get('/settings', [ProfileController::class, 'settings'], ['AuthMiddleware']);
Router::post('/settings', [ProfileController::class, 'updateSettings'], ['AuthMiddleware', 'CsrfMiddleware']);

// Saved Posts
Router::get('/saved', [PostController::class, 'saved'], ['AuthMiddleware']);

// Posts
Router::post('/post/create', [PostController::class, 'create'], ['AuthMiddleware', 'CsrfMiddleware']);
Router::get('/post/{id}', [PostController::class, 'show']);
Router::post('/post/{id}/like', [PostController::class, 'like'], ['AuthMiddleware']);
Router::post('/post/{id}/comment', [PostController::class, 'comment'], ['AuthMiddleware']);
Router::post('/post/{id}/save', [PostController::class, 'save'], ['AuthMiddleware']);
Router::post('/post/{id}/delete', [PostController::class, 'delete'], ['AuthMiddleware']);

// Profile & Follow
Router::get('/profile/edit', [ProfileController::class, 'edit'], ['AuthMiddleware']);
Router::post('/profile/edit', [ProfileController::class, 'edit'], ['AuthMiddleware', 'CsrfMiddleware']);
Router::get('/u/{username}', [ProfileController::class, 'show']);
Router::post('/u/{username}/follow', [ProfileController::class, 'follow'], ['AuthMiddleware']);

// Explore & Search
Router::get('/explore', [ExploreController::class, 'index']);

// Stories
Router::post('/stories/create', [StoryController::class, 'create'], ['AuthMiddleware', 'CsrfMiddleware']);
Router::post('/stories/{id}/view', [StoryController::class, 'view'], ['AuthMiddleware']);

// Messages
Router::get('/messages', [MessageController::class, 'index'], ['AuthMiddleware']);
Router::get('/messages/start/{username}', [MessageController::class, 'start'], ['AuthMiddleware']);
Router::get('/messages/{id}', [MessageController::class, 'index'], ['AuthMiddleware']);
Router::get('/messages/{id}/poll', [MessageController::class, 'poll'], ['AuthMiddleware']);
Router::post('/messages/{id}/send', [MessageController::class, 'send'], ['AuthMiddleware']);

// Notifications
Router::get('/notifications', [NotificationController::class, 'index'], ['AuthMiddleware']);
Router::get('/notifications/unread', [NotificationController::class, 'unreadCount']);

// Admin
Router::get('/admin', [AdminController::class, 'index'], ['AdminMiddleware']);
Router::post('/admin/users/{id}/ban', [AdminController::class, 'toggleBan'], ['AdminMiddleware']);
