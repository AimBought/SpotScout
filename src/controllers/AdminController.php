<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\SpotRepository;
use App\Repository\NotificationRepository;
use App\Repository\ReportRepository;

final class AdminController extends AppController
{
    public function __construct(
        private UserRepository $users,
        private SpotRepository $spots,
        private NotificationRepository $notifications,
        private ReportRepository $reports,
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
        $pendingImageContributions = $this->spots->findPendingImageContributions();
        $pendingImageContributionGroups = $this->spots->findPendingImageContributionGroups();
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
        $this->render('admin', [
            'pageTitle' => 'Admin Panel | SpotScout',
            'users' => $this->users->findAll(),
            'csrfToken' => $this->csrfToken(),
            'activeTab' => 'users',
            'pendingSpots' => $pendingSpots,
            'pendingSpotImages' => $pendingSpotImages,
            'pendingImageContributions' => $pendingImageContributions,
            'pendingImageContributionGroups' => $pendingImageContributionGroups,
            'reportCount' => $this->reports->countOpen(),
        ]);
    }

    public function moderatorTab(): void
    {
        $this->requireRole('admin');
        $pendingSpots = $this->spots->findPendingSpots();
        foreach ($pendingSpots as $spot) {
            $this->notifications->notifyModeratorsAboutPendingSpot((int) $spot->getId(), $spot->getTitle());
        }
        $pendingImageContributions = $this->spots->findPendingImageContributions();
        $pendingImageContributionGroups = $this->spots->findPendingImageContributionGroups();
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
        $this->render('admin', [
            'pageTitle' => 'Pending Spots | SpotScout',
            'users' => [],
            'csrfToken' => $this->csrfToken(),
            'activeTab' => 'spots',
            'pendingSpots' => $pendingSpots,
            'pendingSpotImages' => $pendingSpotImages,
            'pendingImageContributions' => $pendingImageContributions,
            'pendingImageContributionGroups' => $pendingImageContributionGroups,
            'reportCount' => $this->reports->countOpen(),
        ]);
    }

    public function photoTab(): void
    {
        $this->requireRole('admin');
        $pendingSpots = $this->spots->findPendingSpots();
        foreach ($pendingSpots as $spot) {
            $this->notifications->notifyModeratorsAboutPendingSpot((int) $spot->getId(), $spot->getTitle());
        }
        $pendingImageContributions = $this->spots->findPendingImageContributions();
        $pendingImageContributionGroups = $this->spots->findPendingImageContributionGroups();
        foreach ($pendingImageContributions as $contribution) {
            if ($contribution['contributor_user_id'] !== null) {
                $this->notifications->notifyModeratorsAboutPendingImage(
                    $contribution['id'],
                    $contribution['spot_title'],
                    $contribution['contributor_user_id'],
                );
            }
        }

        $this->render('admin', [
            'pageTitle' => 'Pending Photos | SpotScout',
            'users' => [],
            'csrfToken' => $this->csrfToken(),
            'activeTab' => 'photos',
            'pendingSpots' => $pendingSpots,
            'pendingSpotImages' => [],
            'pendingImageContributions' => $pendingImageContributions,
            'pendingImageContributionGroups' => $pendingImageContributionGroups,
            'reportCount' => $this->reports->countOpen(),
        ]);
    }

    public function reportsTab(): void
    {
        $this->requireRole('admin');
        $pendingReports = $this->reports->findPending();
        $escalatedReports = $this->reports->findEscalated();
        $this->render('admin', [
            'pageTitle' => 'Zgłoszenia | SpotScout', 'users' => [], 'csrfToken' => $this->csrfToken(),
            'activeTab' => 'reports', 'pendingSpots' => [], 'pendingSpotImages' => [],
            'pendingImageContributions' => [], 'pendingImageContributionGroups' => [],
            'pendingReports' => $pendingReports, 'escalatedReports' => $escalatedReports,
            'reportCount' => count($pendingReports) + count($escalatedReports),
            'reportImages' => $this->buildReportImages([...$pendingReports, ...$escalatedReports]),
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
