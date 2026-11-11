<?php

declare(strict_types=1);

use App\Controller\DefaultController;
use App\Controller\AdminController;
use App\Controller\ModeratorController;
use App\Controller\ModerationController;
use App\Controller\NotificationController;
use App\Controller\ProfileController;
use App\Controller\SecurityController;
use App\Controller\SpotController;
use App\Database;
use App\Repository\SpotRepository;
use App\Repository\UserRepository;
use App\Repository\NotificationRepository;
use App\Routing;
use App\Service\MailService;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $isHttps,
]);
session_start();

$routing = new Routing();
$userRepository = new UserRepository((new Database())->connect());
$spotRepository = new SpotRepository((new Database())->connect());
$profileController = new ProfileController($userRepository, $spotRepository);
$notificationRepository = new NotificationRepository((new Database())->connect());
$adminController = new AdminController($userRepository, $spotRepository, $notificationRepository);
$moderatorController = new ModeratorController($spotRepository, $notificationRepository);
$moderationController = new ModerationController($spotRepository, $notificationRepository);
$notificationController = new NotificationController();
$mailService = new MailService();
$securityController = new SecurityController($userRepository, $mailService, $spotRepository);
$defaultController = new DefaultController($spotRepository);

$routing->get('/', $defaultController, 'index');
$routing->get('/about', $defaultController, 'about');
$routing->get('/login', $securityController, 'login');
$routing->post('/login', $securityController, 'login');
$routing->get('/register', $securityController, 'register');
$routing->post('/register', $securityController, 'register');
$routing->post('/logout', $securityController, 'logout');
$routing->get('/explore', SpotController::class, 'explore');
$routing->get('/spot/add', SpotController::class, 'add');
$routing->post('/spot/add', SpotController::class, 'add');
$routing->post('/spot/delete/{id}', SpotController::class, 'delete');
$routing->post('/spot/bookmark/{id}', SpotController::class, 'toggleBookmark');
$routing->get('/spot/details/{id}', SpotController::class, 'details');
$routing->post('/spot/contribute/{id}', SpotController::class, 'contribute');
$routing->post('/spot/image/delete/{id}', SpotController::class, 'deleteImage');
$routing->post('/spot/cover/delete/{id}', SpotController::class, 'deleteCoverImage');
$routing->get('/profile', $profileController, 'index');
$routing->post('/profile/avatar', $profileController, 'uploadAvatar');
$routing->get('/admin', $adminController, 'index');
$routing->get('/admin/moderator', $adminController, 'moderatorTab');
$routing->get('/admin/photos', $adminController, 'photoTab');
$routing->post('/admin/role', $adminController, 'changeRole');
$routing->get('/moderator', $moderatorController, 'index');
$routing->get('/moderator/photos', $moderatorController, 'photoTab');
$routing->post('/moderate/approve', $moderationController, 'approve');
$routing->post('/moderate/reject', $moderationController, 'reject');
$routing->post('/moderate/image/approve', $moderationController, 'approveImage');
$routing->post('/moderate/image/reject', $moderationController, 'rejectImage');
$routing->post('/notifications/read', $notificationController, 'markRead');

$routeFound = $routing->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
);

if (!$routeFound) {
    http_response_code(404);
    $defaultController->notFound();
}
