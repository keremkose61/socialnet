<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    private Conversation $convModel;
    private Message $msgModel;

    public function __construct()
    {
        $this->convModel = new Conversation();
        $this->msgModel = new Message();
    }

    public function index(?int $activeConvId = null): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $conversations = $this->convModel->getUserConversations($user['id']);
        $activeMessages = [];
        $activeConversation = null;

        if ($activeConvId) {
            $activeConversation = $this->convModel->find($activeConvId);
            if ($activeConversation) {
                $activeMessages = $this->msgModel->getConversationMessages($activeConvId);
            }
        } elseif (!empty($conversations)) {
            $activeConvId = $conversations[0]['id'];
            $activeConversation = $conversations[0];
            $activeMessages = $this->msgModel->getConversationMessages($activeConvId);
        }

        $this->render('messages/index', [
            'pageTitle'          => 'Mesajlar - SocialNet',
            'currentUser'        => $user,
            'conversations'      => $conversations,
            'activeConvId'       => $activeConvId,
            'activeConversation' => $activeConversation,
            'messages'           => $activeMessages
        ]);
    }

    public function start(string $username): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        $targetUser = (new User())->findByUsername($username);

        if (!$targetUser || (int)$targetUser['id'] === $user['id']) {
            $this->redirect(url('/messages'));
            return;
        }

        $convId = $this->convModel->getOrCreateDirect($user['id'], (int)$targetUser['id']);
        $this->redirect(url('/messages/' . $convId));
    }

    public function send(int $convId): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        $body = trim($this->request->input('body', ''));
        if (empty($body)) {
            $this->json(['success' => false, 'message' => 'Boş mesaj gönderilemez.'], 422);
            return;
        }

        $msgId = $this->msgModel->sendMessage($convId, $user['id'], $body);

        $this->json([
            'success' => true,
            'message' => [
                'id'         => $msgId,
                'sender_id'  => $user['id'],
                'body'       => e($body),
                'avatar'     => upload($user['avatar'] ?? 'default-avatar.png'),
                'created_at' => date('H:i')
            ]
        ]);
    }

    public function poll(int $convId): void
    {
        $this->requireAuth();
        $afterId = (int)$this->request->input('after_id', 0);

        $sql = "SELECT m.*, u.username, u.full_name, prof.avatar as sender_avatar
                FROM `messages` m
                JOIN `users` u ON u.id = m.sender_id
                LEFT JOIN `user_profiles` prof ON prof.user_id = u.id
                WHERE m.conversation_id = :cid AND m.id > :after_id AND m.is_deleted = 0
                ORDER BY m.created_at ASC";

        $newMessages = Database::fetchAll($sql, ['cid' => $convId, 'after_id' => $afterId]);

        $formatted = array_map(function($m) {
            return [
                'id'         => (int)$m['id'],
                'sender_id'  => (int)$m['sender_id'],
                'body'       => e($m['body']),
                'avatar'     => upload($m['sender_avatar'] ?? 'default-avatar.png'),
                'created_at' => date('H:i', strtotime($m['created_at']))
            ];
        }, $newMessages);

        $this->json(['success' => true, 'messages' => $formatted]);
    }
}
