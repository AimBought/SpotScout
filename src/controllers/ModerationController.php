<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Repository\SpotRepository;
use App\Repository\ReportRepository;

final class ModerationController extends AppController
{
    public function __construct(
        private SpotRepository $spots,
        private NotificationRepository $notifications,
        private ReportRepository $reports,
    ) {
    }

    public function approve(): void
    {
        $spot = $this->validatedPendingSpot();
        if ($spot === null) {
            return;
        }
        if (!$this->spots->approve((int) $spot->getId())) {
            http_response_code(409);
            return;
        }
        if ($spot->getUserId() !== null) {
            $this->notifications->create($spot->getUserId(), 'Twój spot „' . $spot->getTitle() . '” został zaakceptowany!');
        }

        $this->redirect($this->queuePath());
    }

    public function reject(): void
    {
        $spot = $this->validatedPendingSpot();
        if ($spot === null) {
            return;
        }
        $reason = trim(is_string($_POST['reason'] ?? null) ? $_POST['reason'] : '');
        if ($reason === '' || mb_strlen($reason) > 1000) {
            http_response_code(400);
            return;
        }
        if (!$this->spots->reject((int) $spot->getId(), $reason)) {
            http_response_code(409);
            return;
        }
        if ($spot->getUserId() !== null) {
            $this->notifications->create($spot->getUserId(), 'Twój spot „' . $spot->getTitle() . '” został odrzucony. Powód: ' . $reason);
        }

        $this->redirect($this->queuePath());
    }

    public function approveImage(): void
    {
        $contribution = $this->validatedPendingImageContribution();
        if ($contribution === null) {
            return;
        }
        $result = $this->spots->moderateImageContribution($contribution['id'], true);
        if ($result === null) {
            http_response_code(409);
            return;
        }
        if ($result['contributor_user_id'] !== null) {
            $this->notifications->create(
                $result['contributor_user_id'],
                'Your photo contribution to "' . $contribution['spot_title'] . '" was approved.'
            );
        }
        $this->redirect($this->photoQueuePath());
    }

    public function rejectImage(): void
    {
        $contribution = $this->validatedPendingImageContribution();
        if ($contribution === null) {
            return;
        }
        $result = $this->spots->moderateImageContribution($contribution['id'], false);
        if ($result === null) {
            http_response_code(409);
            return;
        }
        if ($result['contributor_user_id'] !== null) {
            $this->notifications->create(
                $result['contributor_user_id'],
                'Your photo contribution to "' . $contribution['spot_title'] . '" was rejected.'
            );
        }
        $this->redirect($this->photoQueuePath());
    }

    private function validatedPendingSpot(): ?\App\Model\Spot
    {
        $this->requireRole(['admin', 'moderator']);
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return null;
        }

        $spotId = filter_var($_POST['spot_id'] ?? null, FILTER_VALIDATE_INT);
        if ($spotId === false || $spotId < 1) {
            http_response_code(400);
            return null;
        }
        $spot = $this->spots->find((int) $spotId);
        if ($spot === null || $spot->getStatus() !== 'pending') {
            http_response_code(404);
            return null;
        }
        if (($_SESSION['user_role'] ?? null) === 'moderator' && $spot->getUserId() === (int) $_SESSION['user_id']) {
            http_response_code(403);
            return null;
        }

        return $spot;
    }

    /** @return array{id:int, spot_id:int, image_path:string, spot_title:string, contributor_user_id:?int, contributor_name:?string}|null */
    private function validatedPendingImageContribution(): ?array
    {
        $this->requireRole(['admin', 'moderator']);
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return null;
        }
        $imageId = filter_var($_POST['image_id'] ?? null, FILTER_VALIDATE_INT);
        if ($imageId === false || $imageId < 1) {
            http_response_code(400);
            return null;
        }
        $contribution = $this->spots->findPendingImageContribution((int) $imageId);
        if ($contribution === null) {
            http_response_code(404);
            return null;
        }
        if (($_SESSION['user_role'] ?? null) === 'moderator' && $contribution['contributor_user_id'] === (int) $_SESSION['user_id']) {
            http_response_code(403);
            return null;
        }

        return $contribution;
    }

    private function queuePath(): string
    {
        return ($_SESSION['user_role'] ?? null) === 'admin' ? '/admin/moderator' : '/moderator';
    }

    private function photoQueuePath(): string
    {
        return ($_SESSION['user_role'] ?? null) === 'admin' ? '/admin/photos' : '/moderator/photos';
    }

    public function resolveReport(): void
    {
        $report = $this->validatedReport(['pending'], ['admin', 'moderator']);
        if ($report === null) { return; }
        $type = $report->getReportType();
        if (!in_array($type, ['category', 'location', 'photo'], true)) { http_response_code(400); return; }
        $message = trim(is_string($_POST['owner_message'] ?? null) ? $_POST['owner_message'] : '');
        if ($message === '' || mb_strlen($message) > 1000) { http_response_code(400); return; }
        $spotId = $report->getSpotId();
        if ($type === 'category') {
            $submittedCategories = $_POST['categories'] ?? [];
            $categories = is_array($submittedCategories) ? array_values(array_unique(array_filter($submittedCategories, 'is_string'))) : [];
            $allowedCategories = ['Architecture', 'Industrial', 'Urban', 'Nature', 'Drone Friendly', 'Night'];
            if ($categories === [] || array_diff($categories, $allowedCategories) !== []) { http_response_code(400); return; }
            $updated = $this->spots->updateCategory($spotId, $categories);
        } elseif ($type === 'location') {
            $latitude = $report->getProposedLatitude();
            $longitude = $report->getProposedLongitude();
            if ($latitude === null || $longitude === null) { http_response_code(400); return; }
            $updated = $this->spots->updateLocation($spotId, $latitude, $longitude);
        } else {
            $photoKeys = $report->getProposedPhotoKeys();
            if ($photoKeys === []) { http_response_code(400); return; }
            foreach ($photoKeys as $photoKey) {
                $path = $this->spots->deleteReportedPhoto($spotId, $photoKey);
                if ($path === null) { http_response_code(409); return; }
                $this->removeUploadedFile($path);
            }
            $updated = true;
        }
        if (!$updated || !$this->reports->updateStatus($report->getId(), 'resolved')) { http_response_code(409); return; }
        if ($report->getOwnerId() !== null) {
            $this->notifications->create($report->getOwnerId(), 'Twój spot "' . $report->getSpotTitle() . '" został zmodyfikowany przez moderację. Powód: ' . $message);
        }
        $this->redirect($this->reportsQueuePath());
    }

    public function escalateReport(): void
    {
        $this->requireRole('moderator');
        $report = $this->validatedReport(['pending'], ['moderator']);
        if ($report === null) { return; }
        if ($report->getReportType() !== 'delete') { http_response_code(400); return; }
        if (!$this->reports->updateStatus($report->getId(), 'escalated')) { http_response_code(409); return; }
        $this->notifications->notifyAdmins('Spot "' . $report->getSpotTitle() . '" został oznaczony do usunięcia. (#' . $report->getSpotId() . ')');
        $this->redirect($this->reportsQueuePath());
    }

    public function deleteReportedSpot(): void
    {
        $report = $this->validatedReport(['pending', 'escalated'], ['admin']);
        if ($report === null) { return; }
        if ($report->getReportType() !== 'delete') { http_response_code(400); return; }
        $reason = trim(is_string($_POST['owner_message'] ?? null) ? $_POST['owner_message'] : '');
        if ($reason === '' || mb_strlen($reason) > 1000) { http_response_code(400); return; }
        $spot = $this->spots->find($report->getSpotId());
        if ($spot === null) { http_response_code(404); return; }
        if ($report->getOwnerId() !== null) {
            $this->notifications->create($report->getOwnerId(), 'Twój spot "' . $report->getSpotTitle() . '" został usunięty przez administratora. Powód: ' . $reason);
        }
        // reports.spot_id cascades on spot deletion, so resolve before deleting the spot.
        $this->reports->updateStatus($report->getId(), 'resolved');
        $paths = $this->spots->findImagePaths($spot->getId() ?? 0, $spot->getImagePath());
        $this->spots->delete($report->getSpotId());
        foreach ($paths as $path) { $this->removeUploadedFile($path); }
        $this->redirect('/admin/reports');
    }

    public function rejectReport(): void
    {
        $role = is_string($_SESSION['user_role'] ?? null) ? $_SESSION['user_role'] : '';
        $report = $this->validatedReport(['pending', 'escalated'], ['admin', 'moderator']);
        if ($report === null) { return; }
        if ($role === 'moderator' && $report->getStatus() === 'escalated') { http_response_code(403); return; }
        $message = trim(is_string($_POST['owner_message'] ?? null) ? $_POST['owner_message'] : '');
        if ($message === '' || mb_strlen($message) > 1000) { http_response_code(400); return; }
        if (!$this->reports->updateStatus($report->getId(), 'rejected')) { http_response_code(409); return; }
        $this->notifications->create($report->getReporterId(), 'Twoje zgłoszenie dotyczące spotu "' . $report->getSpotTitle() . '" zostało odrzucone. Powód: ' . $message);
        $this->redirect($this->reportsQueuePath());
    }

    /** @param array<int, string> $statuses @param array<int, string> $roles */
    private function validatedReport(array $statuses, array $roles): ?\App\Model\Report
    {
        $this->requireRole($roles);
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) { http_response_code(403); return null; }
        $id = filter_var($_POST['report_id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) { http_response_code(400); return null; }
        $report = $this->reports->find((int) $id);
        if ($report === null || !in_array($report->getStatus(), $statuses, true)) { http_response_code(404); return null; }
        return $report;
    }

    private function reportsQueuePath(): string
    {
        return ($_SESSION['user_role'] ?? null) === 'admin' ? '/admin/reports' : '/moderator/reports';
    }

    private function removeUploadedFile(string $path): void
    {
        if (!str_starts_with($path, '/uploads/spots/') || basename($path) !== substr($path, strlen('/uploads/spots/'))) { return; }
        $file = dirname(__DIR__, 2) . '/public' . $path;
        if (is_file($file)) { @unlink($file); }
    }
}
