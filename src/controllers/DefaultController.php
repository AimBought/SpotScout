<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\SpotRepository;

/**
 * Kontroler obsługujący strony ogólne: stronę główną, informacje o projekcie
 * oraz stronę błędu 404.
 */
final class DefaultController extends AppController
{
    public function __construct(private SpotRepository $spots)
    {
    }
    /**
     * Wyświetla stronę główną (Welcome / Landing Page) z wyróżnionymi lokacjami
     * oraz statystykami społeczności.
     */
    public function index(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/explore');
        }

        $this->render('home', [
            'pageTitle' => 'SpotScout – Discover Photography & Video Locations',
            'featuredSpot' => $this->spots->findFeaturedSpot(),
        ]);
    }

    /**
     * Wyświetla stronę „O projekcie" (About SpotScout).
     */
    public function about(): void
    {
        $this->render('about', [
            'pageTitle' => 'About | SpotScout',
        ]);
    }

    /**
     * Wyświetla stronę błędu 404 z komunikatem informacyjnym.
     */
    public function notFound(): void
    {
        $this->render('not-found', [
            'pageTitle' => '404 – Page Not Found | SpotScout',
            'errorMessage' => 'Szukana strona lub lokacja nie istnieje w bazie SpotScout.',
        ]);
    }
}
