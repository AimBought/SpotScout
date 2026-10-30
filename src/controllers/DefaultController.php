<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * Kontroler obsługujący strony ogólne: stronę główną, informacje o projekcie
 * oraz stronę błędu 404.
 */
final class DefaultController extends AppController
{
    /**
     * Wyświetla stronę główną (Welcome / Landing Page) z wyróżnionymi lokacjami
     * oraz statystykami społeczności.
     */
    public function index(): void
    {
        $stats = [
            'verifiedSpots' => '2,400+',
            'activeUsers' => '850+',
            'droneFriendly' => '640+',
        ];

        $spots = [
            [
                'id' => 1,
                'name' => 'The Silo Cathedral',
                'location' => 'Dolny Śląsk, PL',
                'coordinates' => '50.8430° N, 16.4880° E',
                'rating' => 4.8,
                'category' => 'Industrial / Architecture',
            ],
            [
                'id' => 2,
                'name' => 'Razorback Mountain Pass',
                'location' => 'Julian Alps, SI',
                'coordinates' => '46.3625° N, 13.8366° E',
                'rating' => 4.9,
                'category' => 'Nature / Viewpoint',
            ],
        ];

        $this->render('home', [
            'pageTitle' => 'SpotScout – Discover Photography & Video Locations',
            'spots' => $spots,
            'stats' => $stats,
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
