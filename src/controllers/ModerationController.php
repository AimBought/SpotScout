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

    private function queuePath(): string
    {
        return ($_SESSION['user_role'] ?? null) === 'admin' ? '/admin/moderator' : '/moderator';
    }
}
