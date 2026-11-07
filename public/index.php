<?php

declare(strict_types=1);

use App\Controller\DefaultController;
use App\Controller\ProfileController;
use App\Controller\SecurityController;
use App\Controller\SpotController;
use App\Database;
use App\Repository\SpotRepository;
use App\Repository\UserRepository;
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
$routing->get('/spot/details/{id}', SpotController::class, 'details');
$routing->get('/profile', $profileController, 'index');
$routing->post('/profile/avatar', $profileController, 'uploadAvatar');

$routeFound = $routing->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
);

if (!$routeFound) {
    http_response_code(404);
    $defaultController->notFound();
}
