<?php

declare(strict_types=1);

use App\Controller\DefaultController;
use App\Controller\SecurityController;
use App\Controller\SpotController;
use App\Routing;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$routing = new Routing();

$routing->get('/', DefaultController::class, 'index');
$routing->get('/about', DefaultController::class, 'about');
$routing->get('/login', SecurityController::class, 'login');
$routing->get('/register', SecurityController::class, 'register');
$routing->get('/explore', SpotController::class, 'explore');
$routing->get('/spot/add', SpotController::class, 'add');
$routing->get('/spot/details/{id}', SpotController::class, 'details');
$routing->get('/profile', SpotController::class, 'profile');

$routeFound = $routing->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
);

if (!$routeFound) {
    http_response_code(404);
    (new DefaultController())->notFound();
}
