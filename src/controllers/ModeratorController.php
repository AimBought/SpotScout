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
        $this->renderPanel('spots');
    }

    public function photoTab(): void
    {
        $this->renderPanel('photos');
    }

    private function renderPanel(string $activeTab): void
    {
        $this->requireRole('moderator');
        $pendingSpots = $this->spots->findPendingSpots((int) $_SESSION['user_id']);
        foreach ($pendingSpots as $spot) {
            $this->notifications->notifyModeratorsAboutPendingSpot((int) $spot->getId(), $spot->getTitle());
        }
        $pendingImageContributions = $this->spots->findPendingImageContributions((int) $_SESSION['user_id']);
        $pendingImageContributionGroups = $this->spots->findPendingImageContributionGroups((int) $_SESSION['user_id']);
        foreach ($pendingImageContributions as $contribution) {
            if ($contribution['contributor_user_id'] !== null) {
                $this->notifications->notifyModeratorsAboutPendingImage(
                    $contribution['id'],
                    $contribution['spot_title'],
                    $contribution['contributor_user_id'],
                );
            }
        }
        $pendingSpotImages = [];
        foreach ($pendingSpots as $spot) {
            $pendingSpotImages[(int) $spot->getId()] = $this->spots->findGalleryImages($spot);
        }
        $this->render('moderator', [
            'pageTitle' => ($activeTab === 'photos' ? 'Pending Photos' : 'Moderator Panel') . ' | SpotScout',
            'activeTab' => $activeTab,
            'pendingSpots' => $pendingSpots,
            'pendingSpotImages' => $pendingSpotImages,
            'pendingImageContributions' => $pendingImageContributions,
            'pendingImageContributionGroups' => $pendingImageContributionGroups,
            'csrfToken' => $this->csrfToken(),
        ]);
    }
}
