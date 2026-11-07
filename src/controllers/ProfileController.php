<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SpotRepository;
use App\Repository\UserRepository;
use RuntimeException;

final class ProfileController extends AppController
{
    public function __construct(
        private UserRepository $users,
        private SpotRepository $spots,
    ) {
    }

    public function index(): void
    {
        $this->requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $user = $this->users->findById($userId);
        if ($user === null) {
            $_SESSION = [];
            $this->redirect('/login');
        }

        $this->render('profile', [
            'pageTitle' => $user->getName() . ' | SpotScout',
            'user' => $user,
            'spots' => $this->spots->findByUserId($userId),
            'csrfToken' => $this->csrfToken(),
            'errors' => [],
        ]);
    }

    public function uploadAvatar(): void
    {
        $this->requireLogin();
        $errors = [];
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            $errors[] = 'Formularz wygasł lub token bezpieczeństwa jest nieprawidłowy.';
        } else {
            try {
                $path = $this->storeAvatar($_FILES['avatar'] ?? null);
                $userId = (int) $_SESSION['user_id'];
                $this->users->updateAvatar($userId, $path);
                $_SESSION['user_avatar_path'] = $path;
                $this->redirect('/profile?avatar=updated');
            } catch (RuntimeException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        $userId = (int) $_SESSION['user_id'];
        $user = $this->users->findById($userId);
        if ($user === null) {
            $this->redirect('/login');
        }
        $this->render('profile', [
            'pageTitle' => $user->getName() . ' | SpotScout',
            'user' => $user,
            'spots' => $this->spots->findByUserId($userId),
            'csrfToken' => $this->csrfToken(),
            'errors' => $errors,
        ]);
    }

    private function storeAvatar(mixed $file): string
    {
        if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) || !is_int($file['error'])) {
            throw new RuntimeException('Wybierz plik JPEG lub PNG.');
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            throw new RuntimeException('Przesłanie awatara nie powiodło się.');
        }
        if ((int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Awatar musi mieć maksymalnie 5 MB.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
        if (!isset($allowed[$extension]) || $mime !== $allowed[$extension] || @getimagesize((string) $file['tmp_name']) === false) {
            throw new RuntimeException('Dozwolone są wyłącznie prawidłowe pliki JPEG i PNG.');
        }

        $directory = dirname(__DIR__, 2) . '/public/uploads/avatars';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Nie można przygotować katalogu na awatary.');
        }
        $filename = uniqid('avatar_', true) . '.' . $extension;
        if (!move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $filename)) {
            throw new RuntimeException('Nie udało się zapisać awatara.');
        }
        return '/uploads/avatars/' . $filename;
    }
}
