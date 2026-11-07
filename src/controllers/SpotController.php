<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\DTO\SpotInputDTO;
use App\Repository\SpotRepository;
use RuntimeException;

final class SpotController extends AppController
{
    private SpotRepository $spots;

    public function __construct()
    {
        $this->spots = new SpotRepository((new Database())->connect());
    }

    public function explore(): void
    {
        $this->requireLogin();

        $spots = $this->spots->findAll();
        $this->render('explore', [
            'pageTitle' => 'SpotScout – Explore Locations',
            'spots' => $spots,
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
            $category = trim(is_string($_POST['category'] ?? null) ? $_POST['category'] : '');
            $latitudeInput = is_string($_POST['latitude'] ?? null) ? $_POST['latitude'] : '';
            $longitudeInput = is_string($_POST['longitude'] ?? null) ? $_POST['longitude'] : '';
            $latitude = filter_var($latitudeInput, FILTER_VALIDATE_FLOAT);
            $longitude = filter_var($longitudeInput, FILTER_VALIDATE_FLOAT);
            if ($title === '' || mb_strlen($title) > 150) $errors[] = 'Podaj tytuł (maksymalnie 150 znaków).';
            if (!in_array($category, ['Architecture', 'Industrial', 'Urban', 'Nature', 'Drone Friendly', 'Night'], true)) $errors[] = 'Wybierz prawidłową kategorię.';
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
                $spotId = $this->spots->save(new SpotInputDTO($title, null, 'active', (float) $latitude, (float) $longitude, $category, $imagePaths[0], (int) $_SESSION['user_id']));
                $this->spots->saveAdditionalImages($spotId, array_slice($imagePaths, 1));
                $this->redirect('/explore');
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
        $this->render('spot-details', [
            'pageTitle' => $spot->getTitle() . ' | SpotScout',
            'spot' => $spot,
            'images' => $this->spots->findImagePaths($id, $spot->getImagePath()),
        ]);
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
