<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SpotRepository;
use App\Repository\NotificationRepository;

final class ModeratorController extends AppController
{
    public function __construct(private SpotRepository $spots, private NotificationRepository $notifications)
    {
    }

    public function index(): void
    {
        $this->requireRole('moderator');
        $pendingSpots = $this->spots->findPendingSpots((int) $_SESSION['user_id']);
        foreach ($pendingSpots as $spot) {
            $this->notifications->notifyModeratorsAboutPendingSpot((int) $spot->getId(), $spot->getTitle());
        }
        $pendingSpotImages = [];
        foreach ($pendingSpots as $spot) {
            $pendingSpotImages[(int) $spot->getId()] = $this->spots->findImagePaths((int) $spot->getId(), $spot->getImagePath());
        }
        $this->render('moderator', [
            'pageTitle' => 'Moderator Panel | SpotScout',
            'pendingSpots' => $pendingSpots,
            'pendingSpotImages' => $pendingSpotImages,
            'csrfToken' => $this->csrfToken(),
        ]);
    }
}
