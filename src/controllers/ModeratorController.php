<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SpotRepository;
use App\Repository\NotificationRepository;
use App\Repository\ReportRepository;

final class ModeratorController extends AppController
{
    public function __construct(private SpotRepository $spots, private NotificationRepository $notifications, private ReportRepository $reports)
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

    public function reportTab(): void
    {
        $this->renderPanel('reports');
    }

    private function renderPanel(string $activeTab): void
    {
        $this->requireRole('moderator');
        $pendingSpots = $this->spots->findPendingSpots((int) $_SESSION['user_id']);
        $pendingReports = $this->reports->findPending();
        foreach ($pendingReports as $report) {
            $this->notifications->notifyModeratorsAboutReport($report->getId(), $report->getSpotTitle(), $report->getReporterId());
        }
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
            'pendingReports' => $pendingReports,
            'escalatedReports' => [],
            'reportImages' => $this->buildReportImages($pendingReports),
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    /** @param array<int, \App\Model\Report> $reports @return array<int, array<int, array{path:string, image_id:?int, is_cover:bool}>> */
    private function buildReportImages(array $reports): array
    {
        $result = [];
        foreach ($reports as $report) {
            $spot = $this->spots->find($report->getSpotId());
            $result[$report->getId()] = $spot === null ? [] : array_map(
                static fn (array $image): array => ['path' => $image['path'], 'image_id' => $image['image_id'], 'is_cover' => $image['is_cover']],
                $this->spots->findGalleryImages($spot),
            );
        }
        return $result;
    }
}
