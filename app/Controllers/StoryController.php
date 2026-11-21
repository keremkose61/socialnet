<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Models\Story;

class StoryController extends Controller
{
    private Story $storyModel;

    public function __construct()
    {
        $this->storyModel = new Story();
    }

    public function create(): void
    {
        $this->requireAuth();
        $user = $this->currentUser();

        if (empty($_FILES['story_media']['name']) || $_FILES['story_media']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Lütfen bir hikâye görseli veya videosu seçin.');
            $this->redirect(url('/'));
            return;
        }

        $tmpName = $_FILES['story_media']['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        $allowedImages = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedVideos = ['video/mp4', 'video/webm'];

        $isImg = in_array($mime, $allowedImages);
        $isVid = in_array($mime, $allowedVideos);

        if (!$isImg && !$isVid) {
            Session::setFlash('error', 'Geçersiz dosya formatı. Sadece JPG, PNG veya MP4 yükleyebilirsiniz.');
            $this->redirect(url('/'));
            return;
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/stories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['story_media']['name'], PATHINFO_EXTENSION);
        $filename = 'story_' . $user['id'] . '_' . time() . '.' . $ext;
        $target = $uploadDir . $filename;

        if (move_uploaded_file($tmpName, $target)) {
            $caption = trim($this->request->input('caption', ''));
            $this->storyModel->createStory(
                userId: $user['id'],
                mediaPath: 'stories/' . $filename,
                mediaType: $isVid ? 'video' : 'image',
                caption: !empty($caption) ? $caption : null
            );

            Session::setFlash('success', 'Hikâyeniz 24 saatliğine yayınlandı!');
        } else {
            Session::setFlash('error', 'Hikâye yüklenirken bir hata oluştu.');
        }

        $this->redirect(url('/'));
    }

    public function view(int $id): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        $this->storyModel->recordView($id, $user['id']);
        $this->json(['success' => true]);
    }
}
