<div class="sn-widget" style="margin-bottom:20px;border-radius:0;border-left:none;border-right:none;border-top:none;">
    <div style="display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="color:#ef4444;"><?= icon('video', 'w-6 h-6') ?></span>
            <div>
                <h1 style="font-size:20px;font-weight:800;letter-spacing:-0.02em;">Canlı Yayın</h1>
                <p style="font-size:13px;color:var(--text-muted);">WebRTC destekli gerçek zamanlı yayın ve izleyici odası</p>
            </div>
        </div>
        <button class="btn-primary" id="btn-start-broadcast" onclick="toggleBroadcast()" style="background:#ef4444;">
            <?= icon('video', 'w-4 h-4') ?>
            <span id="broadcast-btn-text">Yayını Başlat</span>
        </button>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;">
    <!-- Main Video Frame -->
    <div style="background:#000;border-radius:var(--radius-lg);overflow:hidden;position:relative;min-height:460px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border-color);">
        <video id="live-video-player" autoplay playsinline muted style="width:100%;height:100%;object-fit:cover;display:none;"></video>
        
        <div id="video-placeholder" style="text-align:center;color:var(--text-muted);padding:40px;">
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#ef4444;">
                <?= icon('video', 'w-8 h-8') ?>
            </div>
            <h3 style="color:#fff;font-weight:700;font-size:16px;margin-bottom:6px;">Canlı Yayın Bekleniyor</h3>
            <p style="font-size:13px;max-width:280px;margin:0 auto;">Kameranızı açarak yayını hemen başlatabilir veya aktif yayınları izleyebilirsiniz.</p>
        </div>

        <!-- Live Badge -->
        <div id="live-indicator-badge" style="position:absolute;top:16px;left:16px;background:rgba(239,68,68,0.9);color:#fff;font-size:12px;font-weight:800;padding:4px 10px;border-radius:6px;display:none;align-items:center;gap:6px;backdrop-filter:blur(8px);">
            <span style="width:8px;height:8px;border-radius:50%;background:#fff;animation:pulse 1s infinite;"></span>
            <span>CANLI</span>
        </div>
    </div>

    <!-- Live Chat Sidebar -->
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);display:flex;flex-direction:column;height:460px;">
        <div style="padding:14px 16px;border-bottom:1px solid var(--border-color);font-weight:700;font-size:14px;display:flex;align-items:center;justify-content:space-between;">
            <span>Canlı Sohbet</span>
            <span style="font-size:11px;color:var(--text-muted);">1 İzleyici</span>
        </div>

        <div id="live-chat-messages" style="flex:1;overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:10px;">
            <div style="font-size:12px;color:var(--text-muted);text-align:center;margin-top:auto;">
                Sohbete hoş geldiniz! Mesajlarınız burada canlı olarak görünür.
            </div>
        </div>

        <form id="live-chat-form" onsubmit="sendLiveComment(event)" style="padding:12px;border-top:1px solid var(--border-color);display:flex;gap:8px;">
            <input type="text" id="live-chat-input" class="sn-comment-input" placeholder="Bir mesaj yaz..." required autocomplete="off" style="font-size:12.5px;">
            <button type="submit" class="btn-primary" style="padding:8px 14px;"><?= icon('send', 'w-4 h-4') ?></button>
        </form>
    </div>
</div>

<script>
let localStream = null;

async function toggleBroadcast() {
    const video = document.getElementById('live-video-player');
    const placeholder = document.getElementById('video-placeholder');
    const badge = document.getElementById('live-indicator-badge');
    const btnText = document.getElementById('broadcast-btn-text');

    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
        localStream = null;
        video.srcObject = null;
        video.style.display = 'none';
        placeholder.style.display = 'block';
        badge.style.display = 'none';
        btnText.textContent = 'Yayını Başlat';
        showToast('Yayın sonlandırıldı.');
    } else {
        try {
            localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            video.srcObject = localStream;
            video.style.display = 'block';
            placeholder.style.display = 'none';
            badge.style.display = 'flex';
            btnText.textContent = 'Yayını Bitir';
            showToast('Canlı yayın başlatıldı! Kamera ve mikrofon aktif.');
        } catch (err) {
            console.error('Kamera/mikrofon erişim hatası:', err);
            showToast('Kamera veya mikrofona erişilemedi: ' + err.message, 'error');
        }
    }
}

function sendLiveComment(e) {
    e.preventDefault();
    const input = document.getElementById('live-chat-input');
    const text = input.value.trim();
    if (!text) return;

    const box = document.getElementById('live-chat-messages');
    const msg = document.createElement('div');
    msg.style.cssText = 'font-size:12.5px;line-height:1.4;background:var(--bg-secondary);padding:6px 10px;border-radius:8px;';
    msg.innerHTML = `<span style="font-weight:700;color:var(--brand-accent);margin-right:6px;">@<?= e(auth()['username'] ?? 'User') ?>:</span><span>${text}</span>`;
    box.appendChild(msg);
    box.scrollTop = box.scrollHeight;
    input.value = '';
}
</script>
