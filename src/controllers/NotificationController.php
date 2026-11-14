<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Repository\NotificationRepository;

final class NotificationController extends AppController
{
    private NotificationRepository $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationRepository((new Database())->connect());
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->render('notifications', [
            'pageTitle' => 'Powiadomienia | SpotScout',
            'notifications' => $this->notifications->findAllByUserId((int) $_SESSION['user_id']),
        ]);
    }

    public function markRead(): void
    {
        $this->requireLogin();
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(403);
            return;
        }

        $ids = $_POST['notification_ids'] ?? [];
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $notificationId = filter_var($id, FILTER_VALIDATE_INT);
                if ($notificationId !== false && $notificationId > 0) {
                    $this->notifications->markAsRead((int) $notificationId, (int) $_SESSION['user_id']);
                }
            }
        }

        $requestedReturn = is_string($_POST['return_to'] ?? null) ? $_POST['return_to'] : '';
        $returnParts = parse_url($requestedReturn);
        $returnPath = is_array($returnParts) && is_string($returnParts['path'] ?? null) ? $returnParts['path'] : '';
        $allowedPaths = [
            '/explore', '/profile', '/notifications', '/admin', '/admin/moderator', '/admin/photos', '/admin/reports',
            '/moderator', '/moderator/photos', '/moderator/reports', '/spot/add',
        ];
        $isSpotDetailsPath = preg_match('~^/spot/details/[1-9][0-9]*$~', $returnPath) === 1;
        $isSafeReturn = str_starts_with($requestedReturn, '/')
            && !str_starts_with($requestedReturn, '//')
            && !str_contains($requestedReturn, "\r")
            && !str_contains($requestedReturn, "\n")
            && is_array($returnParts)
            && !isset($returnParts['scheme'])
            && !isset($returnParts['host'])
            && !isset($returnParts['user'])
            && !isset($returnParts['pass'])
            && (in_array($returnPath, $allowedPaths, true) || $isSpotDetailsPath);
        $returnTo = $isSafeReturn ? $requestedReturn : '/explore';
        $this->redirect($returnTo);
    }
}
