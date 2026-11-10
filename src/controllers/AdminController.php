<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\SpotRepository;
use App\Repository\NotificationRepository;

final class AdminController extends AppController
{
    public function __construct(
        private UserRepository $users,
        private SpotRepository $spots,
        private NotificationRepository $notifications,
    )
    {
    }

    public function index(): void
    {
        $this->requireRole('admin');
        $pendingSpots = $this->spots->findPendingSpots();
        foreach ($pendingSpots as $spot) {
            $this->notifications->notifyModeratorsAboutPendingSpot((int) $spot->getId(), $spot->getTitle());
        }
        $pendingSpotImages = [];
        foreach ($pendingSpots as $spot) {
            $pendingSpotImages[(int) $spot->getId()] = $this->spots->findImagePaths((int) $spot->getId(), $spot->getImagePath());
        }
        $this->render('admin', [
            'pageTitle' => 'Admin Panel | SpotScout',
            'users' => $this->users->findAll(),
            'csrfToken' => $this->csrfToken(),
            'activeTab' => 'users',
            'pendingSpots' => $pendingSpots,
            'pendingSpotImages' => $pendingSpotImages,
        ]);
    }

    public function moderatorTab(): void
    {
        $this->requireRole('admin');
        $pendingSpots = $this->spots->findPendingSpots();
        foreach ($pendingSpots as $spot) {
            $this->notifications->notifyModeratorsAboutPendingSpot((int) $spot->getId(), $spot->getTitle());
        }
        $pendingSpotImages = [];
        foreach ($pendingSpots as $spot) {
            $pendingSpotImages[(int) $spot->getId()] = $this->spots->findImagePaths((int) $spot->getId(), $spot->getImagePath());
        }
        $this->render('admin', [
            'pageTitle' => 'Pending Spots | SpotScout',
            'users' => [],
            'csrfToken' => $this->csrfToken(),
            'activeTab' => 'pending',
            'pendingSpots' => $pendingSpots,
            'pendingSpotImages' => $pendingSpotImages,
        ]);
    }

    public function changeRole(): void
    {
        $this->requireRole('admin');

        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return;
        }

        $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
        $role = is_string($_POST['role'] ?? null) ? $_POST['role'] : '';
        if ($userId === false || $userId < 1 || !in_array($role, ['user', 'moderator', 'admin'], true)) {
            http_response_code(400);
            return;
        }
        if ($this->users->findById($userId) === null) {
            http_response_code(404);
            return;
        }

        $this->users->updateRole($userId, $role);
        if ($userId === (int) $_SESSION['user_id']) {
            $_SESSION['user_role'] = $role;
        }
        $this->redirect('/admin');
    }
}
