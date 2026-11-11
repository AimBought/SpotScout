<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Repository\SpotRepository;

final class ModerationController extends AppController
{
    public function __construct(
        private SpotRepository $spots,
        private NotificationRepository $notifications,
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
}
