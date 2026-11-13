<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\DTO\SpotInputDTO;
use App\Repository\NotificationRepository;
use App\Repository\SpotRepository;
use App\Repository\ReportRepository;
use DomainException;
use RuntimeException;

final class SpotController extends AppController
{
    private SpotRepository $spots;
    private NotificationRepository $notifications;
    private ReportRepository $reports;

    public function __construct()
    {
        $database = (new Database())->connect();
        $this->spots = new SpotRepository($database);
        $this->notifications = new NotificationRepository($database);
        $this->reports = new ReportRepository($database);
    }

    public function explore(): void
    {
        $this->requireLogin();

        $spots = $this->spots->findAll();
        $this->render('explore', [
            'pageTitle' => 'SpotScout – Explore Locations',
            'spots' => $spots,
            'bookmarkedSpotIds' => $this->spots->findBookmarkedIdsByUserId((int) $_SESSION['user_id']),
            'csrfToken' => $this->csrfToken(),
            'mapSpots' => array_values(array_map(static fn ($spot): array => [
                'title' => $spot->getTitle(),
                'latitude' => $spot->getLatitude(),
                'longitude' => $spot->getLongitude(),
            ], array_filter($spots, static fn ($spot): bool => $spot->getLatitude() !== null && $spot->getLongitude() !== null))),
        ]);
    }

    public function add(): void
    {
        $this->requireLogin();
        $errors = [];

        if ($this->isPost()) {
            $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
            if (!$this->isCsrfTokenValid($token)) {
                http_response_code(403);
                $errors[] = 'Nieprawidłowy token bezpieczeństwa. Odśwież formularz.';
            }

            $title = trim(is_string($_POST['title'] ?? null) ? $_POST['title'] : '');
            $submittedCategories = $_POST['categories'] ?? [];
            $categories = is_array($submittedCategories)
                ? array_values(array_unique(array_filter($submittedCategories, 'is_string')))
                : [];
            $latitudeInput = is_string($_POST['latitude'] ?? null) ? $_POST['latitude'] : '';
            $longitudeInput = is_string($_POST['longitude'] ?? null) ? $_POST['longitude'] : '';
            $latitude = filter_var($latitudeInput, FILTER_VALIDATE_FLOAT);
            $longitude = filter_var($longitudeInput, FILTER_VALIDATE_FLOAT);
            if ($title === '' || mb_strlen($title) > 150) $errors[] = 'Podaj tytuł (maksymalnie 150 znaków).';
            $allowedCategories = ['Architecture', 'Industrial', 'Urban', 'Nature', 'Drone Friendly', 'Night'];
            if ($categories === [] || count($categories) > count($allowedCategories) || array_diff($categories, $allowedCategories) !== []) {
                $errors[] = 'Wybierz co najmniej jedną prawidłową kategorię.';
            }
            if ($latitude === false || $latitude < -90 || $latitude > 90 || $longitude === false || $longitude < -180 || $longitude > 180) $errors[] = 'Wskaż prawidłową lokalizację na mapie.';

            $imagePaths = [];
            if ($errors === []) {
                try {
                    $imagePaths = $this->storeUploadedImages($_FILES['image'] ?? null);
                } catch (RuntimeException $exception) {
                    $errors[] = $exception->getMessage();
                }
            }

            if ($errors === []) {
                $categoryJson = json_encode($categories, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $status = ($_SESSION['user_role'] ?? null) === 'admin' ? 'approved' : 'pending';
                $spotId = $this->spots->save(new SpotInputDTO($title, null, $status, (float) $latitude, (float) $longitude, $categoryJson, $imagePaths[0], (int) $_SESSION['user_id']));
                $this->spots->saveAdditionalImages($spotId, array_slice($imagePaths, 1), (int) $_SESSION['user_id']);
                if ($status === 'pending') {
                    $this->notifications->notifyModeratorsAboutPendingSpot($spotId, $title);
                }
                $this->redirect($status === 'approved' ? '/explore' : '/profile?spot=pending');
            }
        }

        $this->render('add-spot', [
            'pageTitle' => 'Add New Spot | SpotScout',
            'categories' => ['Architecture', 'Industrial', 'Urban', 'Nature', 'Drone Friendly', 'Night'],
            'csrfToken' => $this->csrfToken(),
            'errors' => $errors,
        ]);
    }

    public function details(int $id): void
    {
        $spot = $this->spots->find($id);
        if ($spot === null) {
            http_response_code(404);
            $this->render('not-found', ['pageTitle' => 'Spot not found | SpotScout', 'errorMessage' => 'Spot #' . $id . ' does not exist.']);
            return;
        }
        $isOwner = isset($_SESSION['user_id']) && $spot->getUserId() === (int) $_SESSION['user_id'];
        if ($spot->getStatus() !== 'approved' && !$isOwner && !$this->hasRole(['admin', 'moderator'])) {
            http_response_code(404);
            $this->render('not-found', ['pageTitle' => 'Spot not found | SpotScout', 'errorMessage' => 'Spot #' . $id . ' does not exist.']);
            return;
        }
        $contributionSuccess = is_string($_SESSION['contribution_success'] ?? null) ? $_SESSION['contribution_success'] : null;
        $contributionError = is_string($_SESSION['contribution_error'] ?? null) ? $_SESSION['contribution_error'] : null;
        unset($_SESSION['contribution_success'], $_SESSION['contribution_error']);
        $moderationFlash = is_string($_SESSION['spot_moderation_flash'] ?? null) ? $_SESSION['spot_moderation_flash'] : null;
        unset($_SESSION['spot_moderation_flash']);
        $this->render('spot-details', [
            'pageTitle' => $spot->getTitle() . ' | SpotScout',
            'spot' => $spot,
            'images' => $this->spots->findGalleryImages($spot),
            'ownUnpublishedImages' => isset($_SESSION['user_id']) ? $this->spots->findOwnUnpublishedImages($id, (int) $_SESSION['user_id']) : [],
            'ownImageCount' => isset($_SESSION['user_id']) ? $this->spots->countOwnImages($id, (int) $_SESSION['user_id']) : 0,
            'currentUserId' => isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
            'currentUserRole' => is_string($_SESSION['user_role'] ?? null) ? $_SESSION['user_role'] : null,
            'reportSubmitted' => ($_GET['report'] ?? null) === 'submitted',
            'isBookmarked' => isset($_SESSION['user_id']) && $this->spots->isBookmarkedByUser((int) $_SESSION['user_id'], $id),
            'contributionSuccess' => $contributionSuccess,
            'contributionError' => $contributionError,
            'moderationFlash' => $moderationFlash,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $spotId = filter_var($this->isPost() ? ($_POST['spot_id'] ?? null) : ($_GET['id'] ?? null), FILTER_VALIDATE_INT);
        if ($spotId === false || $spotId < 1) { http_response_code(400); return; }
        $spot = $this->spots->find((int) $spotId);
        if ($spot === null) { http_response_code(404); return; }
        $userId = (int) $_SESSION['user_id'];
        $isOwner = $spot->getUserId() === $userId;
        $isPrivileged = $this->hasRole(['admin', 'moderator']);
        $canEditMetadata = $isOwner || $isPrivileged;
        $images = $this->spots->findGalleryImages($spot);
        $ownUnpublishedImages = $this->spots->findOwnUnpublishedImages((int) $spotId, $userId);
        $canManageOwnPhotos = $ownUnpublishedImages !== [] || count(array_filter(
            $images,
            static fn (array $image): bool => $image['contributor_user_id'] === $userId,
        )) > 0;
        if (!$canEditMetadata && !$canManageOwnPhotos) { http_response_code(403); return; }

        $categories = ['Architecture', 'Industrial', 'Urban', 'Nature', 'Drone Friendly', 'Night'];
        $errors = [];
        $moderationReason = '';
        if ($this->isPost()) {
            if (!$canEditMetadata) { http_response_code(403); return; }
            $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
            if (!$this->isCsrfTokenValid($token)) { http_response_code(403); $errors[] = 'Nieprawidłowy token bezpieczeństwa.'; }
            $moderationReason = trim(is_string($_POST['moderation_reason'] ?? null) ? $_POST['moderation_reason'] : '');
            $moderationReasonRequired = $isPrivileged && !$isOwner;
            if ($moderationReasonRequired && ($moderationReason === '' || mb_strlen($moderationReason) > 1000)) {
                $errors[] = 'Podaj powód zmian (maksymalnie 1000 znaków).';
            }
            $title = trim(is_string($_POST['title'] ?? null) ? $_POST['title'] : '');
            $submitted = $_POST['categories'] ?? [];
            $selectedCategories = is_array($submitted) ? array_values(array_unique(array_filter($submitted, 'is_string'))) : [];
            $latitude = filter_var($_POST['latitude'] ?? null, FILTER_VALIDATE_FLOAT);
            $longitude = filter_var($_POST['longitude'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($title === '' || mb_strlen($title) > 150) { $errors[] = 'Nazwa jest wymagana (maksymalnie 150 znaków).'; }
            if ($selectedCategories === [] || array_diff($selectedCategories, $categories) !== []) { $errors[] = 'Wybierz co najmniej jedną kategorię.'; }
            if ($latitude === false || $longitude === false || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) { $errors[] = 'Wskaż prawidłową lokalizację na mapie.'; }
            if ($errors === []) {
                if (!$this->spots->updateMetadata((int) $spotId, $title, $selectedCategories, (float) $latitude, (float) $longitude)) { http_response_code(409); return; }
                if ($spot->getUserId() !== null && $spot->getUserId() !== $userId && $isPrivileged) {
                    $this->notifications->create($spot->getUserId(), 'Twój spot „' . $title . '” został zaktualizowany przez moderację. Powód: ' . $moderationReason);
                }
                $this->redirect('/spot/details/' . $spotId);
            }
            $spot->setTitle($title);
            $spot->setCategory(json_encode($selectedCategories, JSON_UNESCAPED_UNICODE) ?: '[]');
            if ($latitude !== false) { $spot->setLatitude((float) $latitude); }
            if ($longitude !== false) { $spot->setLongitude((float) $longitude); }
        }
        $this->render('edit-spot', [
            'pageTitle' => 'Edytuj spot | SpotScout',
            'spot' => $spot,
            'categories' => $categories,
            'images' => $images,
            'ownUnpublishedImages' => $ownUnpublishedImages,
            'canEditMetadata' => $canEditMetadata,
            'moderationReasonRequired' => $isPrivileged && !$isOwner,
            'moderationReason' => $moderationReason,
            'currentUserRole' => is_string($_SESSION['user_role'] ?? null) ? $_SESSION['user_role'] : null,
            'currentUserId' => $userId,
            'errors' => $errors,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function deletePhoto(): void
    {
        $this->requireLogin();
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) { http_response_code(403); return; }
        $spotId = filter_var($_POST['spot_id'] ?? null, FILTER_VALIDATE_INT);
        $photoKey = is_string($_POST['photo_key'] ?? null) ? $_POST['photo_key'] : '';
        if ($spotId === false || $spotId < 1 || !preg_match('/^(cover|[1-9][0-9]*)$/', $photoKey)) { http_response_code(400); return; }
        $spot = $this->spots->find((int) $spotId);
        $photo = $this->spots->findPhotoOwnership((int) $spotId, $photoKey);
        if ($spot === null || $photo === null) { http_response_code(404); return; }
        $userId = (int) $_SESSION['user_id'];
        $privileged = $this->hasRole(['admin', 'moderator']);
        if (!$privileged && $spot->getUserId() !== $userId && $photo['author_id'] !== $userId) { http_response_code(403); return; }
        $moderationReasonRequired = $privileged && $spot->getUserId() !== $userId;
        $reason = trim(is_string($_POST['reason'] ?? null) ? $_POST['reason'] : '');
        if ($moderationReasonRequired && ($reason === '' || mb_strlen($reason) > 1000)) { http_response_code(400); return; }
        $deleted = $this->spots->deletePhotoForModeration((int) $spotId, $photoKey);
        if ($deleted === null) { http_response_code(409); return; }
        $this->removeSpotImageFile($deleted['image_path']);
        if ($deleted['author_id'] !== null) {
            $message = $moderationReasonRequired
                ? 'Jedno ze zdjęć w spocie „' . $spot->getTitle() . '” zostało usunięte przez moderację. Powód: ' . $reason
                : ($privileged
                    ? 'Jedno ze zdjęć w spocie „' . $spot->getTitle() . '” zostało usunięte przez moderację.'
                    : 'Jedno ze zdjęć w spocie „' . $spot->getTitle() . '” zostało usunięte.');
            $this->notifications->create($deleted['author_id'], $message);
        }
        $this->redirect($privileged || $spot->getUserId() === $userId ? '/spot/edit?id=' . $spotId : '/spot/details/' . $spotId);
    }

    public function report(): void
    {
        $this->requireRole('user');
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return;
        }
        $id = filter_var($_POST['spot_id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) { http_response_code(400); return; }
        $spot = $this->spots->find((int) $id);
        if ($spot === null || $spot->getStatus() !== 'approved') {
            http_response_code(404);
            return;
        }
        $type = is_string($_POST['report_type'] ?? null) ? $_POST['report_type'] : '';
        $description = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');
        if (!in_array($type, ['category', 'location', 'photo', 'delete'], true) || $description === '' || mb_strlen($description) > 2000) {
            http_response_code(400);
            return;
        }
        $proposedLatitude = null;
        $proposedLongitude = null;
        $proposedPhotoKeys = [];
        if ($type === 'location') {
            $latitude = filter_var($_POST['proposed_latitude'] ?? null, FILTER_VALIDATE_FLOAT);
            $longitude = filter_var($_POST['proposed_longitude'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($latitude === false || $longitude === false || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                http_response_code(400);
                return;
            }
            $proposedLatitude = (float) $latitude;
            $proposedLongitude = (float) $longitude;
        }
        if ($type === 'photo') {
            $submittedPhotoKeys = $_POST['photo_keys'] ?? [];
            if (!is_array($submittedPhotoKeys)) { http_response_code(400); return; }
            $proposedPhotoKeys = array_values(array_unique(array_filter($submittedPhotoKeys, 'is_string')));
            $gallery = $this->spots->findGalleryImages($spot);
            $availablePhotoKeys = array_map(
                static fn (array $image): string => $image['is_cover'] ? 'cover' : (string) $image['image_id'],
                $gallery,
            );
            if ($proposedPhotoKeys === [] || array_diff($proposedPhotoKeys, $availablePhotoKeys) !== []) {
                http_response_code(400);
                return;
            }
        }
        $reporterId = (int) $_SESSION['user_id'];
        $reportId = $this->reports->create((int) $id, $reporterId, $type, $description, $proposedLatitude, $proposedLongitude, $proposedPhotoKeys);
        $this->notifications->notifyModeratorsAboutReport($reportId, $spot->getTitle(), $reporterId);
        $this->redirect('/spot/details/' . $id . '?report=submitted');
    }

    public function contribute(int $id): void
    {
        $this->requireLogin();
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return;
        }

        $spot = $this->spots->find($id);
        if ($spot === null || $spot->getStatus() !== 'approved') {
            http_response_code(404);
            return;
        }

        try {
            $imagePaths = $this->storeUploadedImages($_FILES['images'] ?? null);
        } catch (RuntimeException $exception) {
            $_SESSION['contribution_error'] = $exception->getMessage();
            $this->redirect('/spot/details/' . $id);
        }

        $userId = (int) $_SESSION['user_id'];
        $status = ($_SESSION['user_role'] ?? null) === 'admin' ? 'approved' : 'pending';
        try {
            $imageIds = $this->spots->addImageContribution($id, $userId, $imagePaths, $status);
        } catch (DomainException $exception) {
            foreach ($imagePaths as $path) {
                @unlink(dirname(__DIR__, 2) . '/public' . $path);
            }
            $_SESSION['contribution_error'] = $exception->getMessage();
            $this->redirect('/spot/details/' . $id);
        }
        if ($status === 'pending') {
            foreach ($imageIds as $imageId) {
                $this->notifications->notifyModeratorsAboutPendingImage($imageId, $spot->getTitle(), $userId);
            }
        }
        $_SESSION['contribution_success'] = $status === 'approved'
            ? 'Zdjęcia zostały dodane do galerii.'
            : 'Zdjęcia zostały wysłane do weryfikacji moderatora.';
        $this->redirect('/spot/details/' . $id);
    }

    public function deleteImage(int $id): void
    {
        $this->requireLogin();
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return;
        }

        $deleted = $this->spots->deleteOwnImage($id, (int) $_SESSION['user_id']);
        if ($deleted === null) {
            http_response_code(404);
            return;
        }

        $this->removeSpotImageFile($deleted['image_path']);
        $_SESSION['contribution_success'] = 'Twoje zdjęcie zostało usunięte.';
        $this->redirect('/spot/details/' . $deleted['spot_id']);
    }

    public function deleteCoverImage(int $id): void
    {
        $this->requireLogin();
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return;
        }

        $deleted = $this->spots->deleteOwnCoverImage($id, (int) $_SESSION['user_id']);
        if ($deleted === null) {
            http_response_code(404);
            return;
        }

        $this->removeSpotImageFile($deleted['image_path']);
        $_SESSION['contribution_success'] = 'Twoje zdjęcie zostało usunięte.';
        $this->redirect('/spot/details/' . $deleted['spot_id']);
    }

    private function removeSpotImageFile(string $path): void
    {
        if (!str_starts_with($path, '/uploads/spots/') || basename($path) !== substr($path, strlen('/uploads/spots/'))) {
            return;
        }
        $filePath = dirname(__DIR__, 2) . '/public' . $path;
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    public function delete(int $id): void
    {
        $this->requireLogin();

        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return;
        }

        $userId = (int) $_SESSION['user_id'];
        $spot = $this->spots->find($id);
        if ($spot === null) {
            http_response_code(404);
            return;
        }
        if ($spot->getUserId() !== $userId && !$this->hasRole(['admin', 'moderator'])) {
            http_response_code(403);
            return;
        }

        $isOwner = $spot->getUserId() === $userId;
        $isModeratorEscalation = $this->hasRole('moderator') && !$isOwner;
        $isAdminDeletion = $this->hasRole('admin') && !$isOwner;
        $reason = trim(is_string($_POST['reason'] ?? null) ? $_POST['reason'] : '');
        if (($isModeratorEscalation || $isAdminDeletion) && ($reason === '' || mb_strlen($reason) > 1000)) {
            http_response_code(400);
            return;
        }

        if ($isModeratorEscalation) {
            $this->reports->createEscalatedDelete($id, $userId, $reason);
            $this->notifications->notifyAdmins('Spot „' . $spot->getTitle() . '” został oznaczony do usunięcia. Powód: ' . $reason);
            $_SESSION['spot_moderation_flash'] = 'Spot oznaczono do usunięcia. Oczekuje na decyzję Admina.';
            $this->redirect('/spot/details/' . $id);
        }

        if ($isAdminDeletion && $spot->getUserId() !== null) {
            $this->notifications->create(
                $spot->getUserId(),
                'Twój spot „' . $spot->getTitle() . '” został usunięty przez Administratora. Powód: ' . $reason
            );
        }

        $imagePaths = $this->spots->findImagePaths($id, $spot->getImagePath());
        if ($spot->getUserId() === $userId) {
            if (!$this->spots->deleteOwnedByUser($id, $userId)) {
                http_response_code(404);
                return;
            }
        } else {
            $this->spots->delete($id);
        }

        $uploadsDirectory = dirname(__DIR__, 2) . '/public/uploads/spots/';
        foreach ($imagePaths as $imagePath) {
            $relativePath = substr($imagePath, strlen('/uploads/spots/'));
            if (str_starts_with($imagePath, '/uploads/spots/') && $relativePath !== '' && basename($relativePath) === $relativePath) {
                $filePath = $uploadsDirectory . $relativePath;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        $this->redirect('/explore');
    }

    public function toggleBookmark(int $id): void
    {
        $this->requireLogin();

        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return;
        }
        if ($this->spots->find($id) === null) {
            http_response_code(404);
            return;
        }

        $this->spots->toggleBookmark((int) $_SESSION['user_id'], $id);
        $requestedReturn = $_POST['return_to'] ?? '';
        $allowedReturns = ['/explore', '/profile', '/spot/details/' . $id];
        $returnTo = is_string($requestedReturn) && in_array($requestedReturn, $allowedReturns, true)
            ? $requestedReturn
            : '/explore';
        $this->redirect($returnTo);
    }

    public function profile(): void
    {
        $this->requireLogin();
        $this->redirect('/explore');
    }

    /** Validate image bytes and store a uniquely named JPEG or PNG. */
    private function storeUploadedImage(mixed $file): string
    {
        if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'], $file['name']) || !is_int($file['error'])) {
            throw new RuntimeException('Dodaj zdjęcie w formacie JPEG lub PNG.');
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            throw new RuntimeException('Przesyłanie zdjęcia nie powiodło się.');
        }
        if ((int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Zdjęcie musi mieć maksymalnie 5 MB.');
        }

        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
        $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
        if (!isset($allowed[$extension]) || $mime !== $allowed[$extension] || @getimagesize((string) $file['tmp_name']) === false) {
            throw new RuntimeException('Dozwolone są wyłącznie prawidłowe pliki JPEG i PNG.');
        }

        $directory = dirname(__DIR__, 2) . '/public/uploads/spots';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Nie można przygotować katalogu na zdjęcia.');
        }
        $filename = uniqid('spot_', true) . '.' . $extension;
        if (!move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $filename)) {
            throw new RuntimeException('Nie udało się zapisać zdjęcia.');
        }
        return '/uploads/spots/' . $filename;
    }

    /** @return array<int, string> */
    private function storeUploadedImages(mixed $files): array
    {
        if (!is_array($files) || !isset($files['name'], $files['type'], $files['tmp_name'], $files['error'], $files['size']) || !is_array($files['name'])) {
            throw new RuntimeException('Dodaj co najmniej jedno zdjęcie.');
        }
        $count = count($files['name']);
        if ($count < 1 || $count > 5) {
            throw new RuntimeException('Możesz dodać od 1 do 5 zdjęć.');
        }

        $paths = [];
        try {
            for ($index = 0; $index < $count; $index++) {
                $paths[] = $this->storeUploadedImage([
                    'name' => $files['name'][$index] ?? '',
                    'tmp_name' => $files['tmp_name'][$index] ?? '',
                    'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$index] ?? 0,
                ]);
            }
        } catch (RuntimeException $exception) {
            foreach ($paths as $path) {
                @unlink(dirname(__DIR__, 2) . '/public' . $path);
            }
            throw $exception;
        }
        return $paths;
    }
}
