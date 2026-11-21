<div class="sn-chat-layout">
    <!-- LEFT PANE: CONVERSATION LIST -->
    <div class="sn-chat-inbox">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:18px;font-weight:800;letter-spacing:-0.02em;">Sohbetler</h2>
            <span style="font-size:12px;color:var(--text-muted);font-weight:700;"><?= count($conversations) ?> Sohbet</span>
        </div>

        <div style="overflow-y:auto;flex:1;">
            <?php if (empty($conversations)): ?>
                <div style="padding:40px 20px;text-align:center;color:var(--text-muted);font-size:13.5px;">
                    Henüz aktif bir sohbetiniz yok. Bir profil üzerinden mesaj başlatabilirsiniz.
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): $isActive = ($activeConvId == $conv['id']); ?>
                <a href="<?= url('/messages/' . $conv['id']) ?>" class="sn-chat-thread-item <?= $isActive ? 'active' : '' ?>">
                    <div style="position:relative;">
                        <?= avatar_img($conv['recipient_avatar'] ?? null, $conv['recipient_name'] ?? 'Kullanıcı', 'sn-avatar', 'width:44px;height:44px;') ?>
                        <span style="position:absolute;bottom:1px;right:1px;width:10px;height:10px;background:var(--success);border-radius:50%;border:2px solid var(--bg-card);"></span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px;">
                            <span style="font-weight:700;font-size:14px;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($conv['recipient_name'] ?? 'Kullanıcı') ?></span>
                            <span style="font-size:11px;color:var(--text-muted);"><?= !empty($conv['last_message_time']) ? date('H:i', strtotime($conv['last_message_time'])) : '' ?></span>
                        </div>
                        <div style="font-size:12.5px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?= e($conv['last_message_body'] ?? 'Sohbet başlatıldı.') ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- CENTER PANE: ACTIVE CHAT -->
    <div class="sn-chat-main">
        <?php if ($activeConversation): ?>
            <!-- Chat Header -->
            <div class="sn-chat-header">
                <div style="display:flex;align-items:center;gap:12px;">
                    <a href="<?= url('/u/' . ($activeConversation['recipient_username'] ?? '')) ?>">
                        <?= avatar_img($activeConversation['recipient_avatar'] ?? null, $activeConversation['recipient_name'] ?? 'Kullanıcı', 'sn-avatar', 'width:38px;height:38px;') ?>
                    </a>
                    <div>
                        <a href="<?= url('/u/' . ($activeConversation['recipient_username'] ?? '')) ?>" style="font-size:15px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:4px;">
                            <span><?= e($activeConversation['recipient_name'] ?? 'Sohbet') ?></span>
                        </a>
                        <div style="font-size:12px;color:var(--success);display:flex;align-items:center;gap:4px;">
                            <span style="width:6px;height:6px;background:var(--success);border-radius:50%;"></span>
                            <span>Çevrimiçi</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:8px;">
                    <a href="<?= url('/u/' . ($activeConversation['recipient_username'] ?? '')) ?>" class="btn-secondary" style="font-size:12px;padding:6px 14px;">Profili Gör</a>
                </div>
            </div>

            <!-- Messages Stream -->
            <div class="sn-chat-stream" id="messages-stream">
                <?php if (empty($messages)): ?>
                    <div style="margin:auto;text-align:center;color:var(--text-muted);font-size:14px;">
                        <div><?= icon('message', 'w-10 h-10') ?></div>
                        <div style="margin-top:8px;">Henüz hiç mesaj yok. Merhaba de! 👋</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): $isMe = ((int)$msg['sender_id'] === (int)$currentUser['id']); ?>
                    <div class="msg-row" data-msg-id="<?= $msg['id'] ?>" style="display:flex;align-items:flex-end;gap:8px;<?= $isMe ? 'justify-content:flex-end;' : 'justify-content:flex-start;' ?>">
                        <?php if (!$isMe): ?>
                            <?= avatar_img($msg['sender_avatar'] ?? null, $msg['sender_name'] ?? 'Kullanıcı', 'sn-avatar', 'width:28px;height:28px;') ?>
                        <?php endif; ?>
                        <div class="sn-msg-bubble <?= $isMe ? 'me' : 'other' ?>">
                            <?= nl2br(e($msg['body'])) ?>
                            <div style="font-size:10.5px;color:<?= $isMe ? 'rgba(255,255,255,0.7)' : 'var(--text-muted)' ?>;text-align:right;margin-top:4px;">
                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Input Bar -->
            <form id="active-chat-form" class="sn-chat-input-bar">
                <input type="text" id="chat-msg-input" class="sn-search-input" style="border-radius:var(--radius-full);padding:11px 18px;" placeholder="Bir mesaj yazın..." required autocomplete="off" autofocus>
                <button type="submit" class="btn-primary" style="padding:10px 20px;border-radius:var(--radius-full);">
                    <?= icon('send', 'w-4 h-4') ?>
                    <span>Gönder</span>
                </button>
            </form>

            <script>
            const stream = document.getElementById('messages-stream');
            if (stream) stream.scrollTop = stream.scrollHeight;

            document.getElementById('active-chat-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const input = document.getElementById('chat-msg-input');
                const body = input.value.trim();
                if (!body) return;

                try {
                    const res = await fetch(`<?= url('/messages/' . $activeConvId . '/send') ?>`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_token() ?>',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ body })
                    });
                    const data = await res.json();
                    if (data.success) {
                        input.value = '';
                        const row = document.createElement('div');
                        row.className = 'msg-row';
                        row.dataset.msgId = data.message.id;
                        row.style.cssText = 'display:flex;align-items:flex-end;gap:8px;justify-content:flex-end;';
                        row.innerHTML = `
                            <div class="sn-msg-bubble me">
                                ${data.message.body}
                                <div style="font-size:10.5px;color:rgba(255,255,255,0.7);text-align:right;margin-top:4px;">${data.message.created_at}</div>
                            </div>
                        `;
                        stream.appendChild(row);
                        stream.scrollTop = stream.scrollHeight;
                    }
                } catch (err) {
                    console.error(err);
                }
            });

            // Polling for live incoming messages
            let lastMsgId = 0;
            const rows = document.querySelectorAll('.msg-row');
            if (rows.length > 0) {
                lastMsgId = parseInt(rows[rows.length - 1].dataset.msgId) || 0;
            }

            setInterval(async () => {
                try {
                    const res = await fetch(`<?= url('/messages/' . $activeConvId . '/poll') ?>?after_id=${lastMsgId}`);
                    const data = await res.json();
                    if (data.success && data.messages && data.messages.length > 0) {
                        data.messages.forEach(m => {
                            if (m.sender_id != <?= (int)$currentUser['id'] ?>) {
                                const row = document.createElement('div');
                                row.className = 'msg-row';
                                row.dataset.msgId = m.id;
                                row.style.cssText = 'display:flex;align-items:flex-end;gap:8px;justify-content:flex-start;';
                                row.innerHTML = `
                                    <img src="${m.avatar}" class="sn-avatar" style="width:28px;height:28px;">
                                    <div class="sn-msg-bubble other">
                                        ${m.body}
                                        <div style="font-size:10.5px;color:var(--text-muted);text-align:right;margin-top:4px;">${m.created_at}</div>
                                    </div>
                                `;
                                stream.appendChild(row);
                                stream.scrollTop = stream.scrollHeight;
                            }
                            lastMsgId = Math.max(lastMsgId, m.id);
                        });
                    }
                } catch (_) {}
            }, 3000);
            </script>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;flex:1;color:var(--text-muted);gap:12px;">
                <div style="width:64px;height:64px;border-radius:50%;background:var(--bg-surface);display:flex;align-items:center;justify-content:center;color:var(--brand-primary);">
                    <?= icon('message', 'w-8 h-8') ?>
                </div>
                <h3 style="font-size:18px;font-weight:700;color:var(--text-primary);">Mesajlarınız</h3>
                <p style="font-size:14px;max-width:280px;text-align:center;">Sohbete başlamak için soldaki listeden bir konuşma seçin.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
