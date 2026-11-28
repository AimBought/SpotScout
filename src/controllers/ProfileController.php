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

        $errors = is_array($_SESSION['profile_errors'] ?? null) ? $_SESSION['profile_errors'] : [];
        unset($_SESSION['profile_errors']);

        $this->render('profile', [
            'pageTitle' => $user->getName() . ' | SpotScout',
            'user' => $user,
            'spots' => $this->spots->findByUserId($userId),
            'bookmarkedSpots' => $this->spots->findBookmarkedByUserId($userId),
            'csrfToken' => $this->csrfToken(),
            'errors' => $errors,
        ]);
    }

    public function publicProfile(int $id): void
    {
        if ($id < 1) {
            http_response_code(404);
            $this->render('not-found', ['pageTitle' => 'Nie znaleziono użytkownika | SpotScout']);
            return;
        }

        if (isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] === $id) {
            $this->redirect('/profile');
        }

        $user = $this->users->findById($id);
        if ($user === null) {
            http_response_code(404);
            $this->render('not-found', ['pageTitle' => 'Nie znaleziono użytkownika | SpotScout']);
            return;
        }

        $this->render('public-profile', [
            'pageTitle' => $user->getName() . ' | Profil SpotScout',
            'user' => $user,
            'spots' => $this->spots->findApprovedByUserId($id),
        ]);
    }

    public function updateBio(): void
    {
        $this->requireLogin();
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            $_SESSION['profile_errors'] = ['Formularz wygasł lub token bezpieczeństwa jest nieprawidłowy.'];
            $this->redirect('/profile');
        }

        $bio = trim(is_string($_POST['bio'] ?? null) ? $_POST['bio'] : '');
        if (mb_strlen($bio) > 500) {
            $_SESSION['profile_errors'] = ['Opis może mieć maksymalnie 500 znaków.'];
            $this->redirect('/profile');
        }

        $this->users->updateBio((int) $_SESSION['user_id'], $bio === '' ? null : $bio);
        $this->redirect('/profile?bio=updated');
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
            'bookmarkedSpots' => $this->spots->findBookmarkedByUserId($userId),
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
