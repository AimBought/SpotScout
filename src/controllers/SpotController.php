<?php

declare(strict_types=1);

namespace App\Controller;

final class SpotController extends AppController
{
    private function getSpotsData(): array
    {
        return [
            1 => [
                'id' => 1,
                'name' => 'The Silo Cathedral',
                'subtitle' => 'Verdant Quarry',
                'location' => 'Dolny Śląsk, PL',
                'distance' => '2.4 km',
                'coordinates' => '50.8430° N, 16.4880° E',
                'rating' => 4.8,
                'reviewsCount' => 42,
                'saves' => 124,
                'author' => '@Adam_K',
                'badges' => ['Architecture', 'Drone Friendly', 'Tripod'],
                'description' => 'Opuszczone silosy w starym kamieniołomie. Świetne, dramatyczne światło poranne wpadające przez otwory w dachu oraz stabilne betonowe podłoże pod statywy.',
                'bestLight' => 'Golden Hour (06:15 - 07:30)',
                'accessInfo' => 'Swobodny dostęp pieszo, parking 200m od wejścia.',
                'notes' => [
                    [
                        'author' => 'Adam Kowalski (@Adam_K)',
                        'role' => 'Photographer / Filmmaker',
                        'date' => 'Yesterday',
                        'rating' => 5.0,
                        'gear' => '35mm f/1.4',
                        'comment' => 'Najlepsze światło między 06:15 a 07:30 rano. Betonowe podłoże jest równe i bezpieczne pod cięższy statyw.',
                    ],
                    [
                        'author' => 'Marcus Vance',
                        'role' => 'Drone Operator',
                        'date' => '3 days ago',
                        'rating' => 4.5,
                        'gear' => 'DJI Mavic / 24mm',
                        'comment' => 'Świetny klimat o zachodzie słońca. Warto zabrać filtr ND i szeroki obiektyw.',
                    ],
                ],
            ],
            2 => [
                'id' => 2,
                'name' => 'Razorback Mountain Pass',
                'subtitle' => 'Alpine Ridge',
                'location' => 'Julian Alps, SI',
                'distance' => '8.7 km',
                'coordinates' => '46.3625° N, 13.8366° E',
                'rating' => 4.9,
                'reviewsCount' => 88,
                'saves' => 219,
                'author' => '@Elena_V',
                'badges' => ['Nature', 'Drone Friendly'],
                'description' => 'Punkt widokowy z panoramą 360 stopni na pasmo górskie. Idealne miejsce na zdjęcia krajobrazowe o wschodzie słońca oraz ujęcia z drona.',
                'bestLight' => 'Sunrise / Blue Hour',
                'accessInfo' => 'Dojście szlakiem około 30 minut z górnego parkingu.',
                'notes' => [
                    [
                        'author' => 'Elena Rostova (@Elena_V)',
                        'role' => 'Landscape Photographer',
                        'date' => '5 days ago',
                        'rating' => 5.0,
                        'gear' => '24-70mm f/2.8',
                        'comment' => 'Czysty horyzont w każdą stronę, jesienią często pojawiają się malownicze mgły w dolinie.',
                    ],
                ],
            ],
            3 => [
                'id' => 3,
                'name' => 'Neon Arcade Alley',
                'subtitle' => 'Shinjuku District',
                'location' => 'Shinjuku, JP',
                'distance' => '14.2 km',
                'coordinates' => '35.6938° N, 139.7034° E',
                'rating' => 4.7,
                'reviewsCount' => 64,
                'saves' => 305,
                'author' => '@Kenji_Cine',
                'badges' => ['Urban', 'Night'],
                'description' => 'Klimatyczna miejska uliczka pełna kolorowych neonów. Po deszczu asfalt tworzy efektowne odbicia światła, doskonałe do nocnych sesji portretowych i wideo.',
                'bestLight' => 'Night (po godz. 20:00)',
                'accessInfo' => 'Miejsce publiczne dostępne 24/7, duży ruch pieszych wieczorami.',
                'notes' => [],
            ],
            4 => [
                'id' => 4,
                'name' => 'Oder River Overlook',
                'subtitle' => 'North Embankment',
                'location' => 'Wrocław, PL',
                'distance' => '4.1 km',
                'coordinates' => '51.1079° N, 17.0385° E',
                'rating' => 4.8,
                'reviewsCount' => 53,
                'saves' => 195,
                'author' => '@adam_cine',
                'badges' => ['Urban', 'Architecture', 'Drone Friendly'],
                'description' => 'Nabrzeże z szerokim widokiem na rzekę i zabytkową architekturę miasta. Spokojne miejsce na wieczorne zdjęcia z długim czasem naświetlania.',
                'bestLight' => 'Sunset (19:30 - 20:15)',
                'accessInfo' => 'Bezpośredni dojazd i ścieżka rowerowa wzdłuż rzeki.',
                'notes' => [],
            ],
            5 => [
                'id' => 5,
                'name' => 'Turbine Plant No. 4',
                'subtitle' => 'Industrial Complex',
                'location' => 'Katowice, PL',
                'distance' => '8.3 km',
                'coordinates' => '50.2649° N, 19.0238° E',
                'rating' => 4.9,
                'reviewsCount' => 71,
                'saves' => 312,
                'author' => '@adam_cine',
                'badges' => ['Industrial', 'Architecture', 'Tripod'],
                'description' => 'Dawna hala turbin z zachowaną stalową konstrukcją i wysokimi oknami. Surowy, industrialny klimat na sesje zdjęciowe i teledyski.',
                'bestLight' => 'Midday (12:00 - 15:00)',
                'accessInfo' => 'Wejście po wcześniejszym zgłoszeniu u zarządcy obiektu.',
                'notes' => [],
            ],
        ];
    }

    public function explore(): void
    {
        $spots = $this->getSpotsData();

        $categories = [
            ['name' => 'All Locations', 'count' => 128],
            ['name' => 'Bookmarked', 'count' => 19],
            ['name' => 'Architecture', 'count' => 43],
            ['name' => 'Nature', 'count' => 32],
            ['name' => 'Urban', 'count' => 28],
            ['name' => 'Drone Friendly', 'count' => 64],
            ['name' => 'Night', 'count' => 18],
        ];

        $nearbyMapPins = [
            ['name' => 'Silo Cathedral', 'distance' => '2.4 km'],
            ['name' => 'Old Quarry', 'distance' => '3.8 km'],
            ['name' => 'Oder Overlook', 'distance' => '4.1 km'],
        ];

        $this->render('explore', [
            'pageTitle' => 'SpotScout – Explore Locations Feed',
            'categories' => $categories,
            'nearbyMapPins' => $nearbyMapPins,
            'spots' => $spots,
            'totalSpots' => 128,
        ]);
    }

    public function details(int $id): void
    {
        $spots = $this->getSpotsData();

        if (!isset($spots[$id])) {
            http_response_code(404);
            $this->render('not-found', [
                'pageTitle' => '404 – Spot Not Found | SpotScout',
                'errorMessage' => 'Lokacja o identyfikatorze #' . $id . ' nie istnieje w bazie danych.',
            ]);
            return;
        }

        $this->render('spot-details', [
            'pageTitle' => $spots[$id]['name'] . ' – Spot Details | SpotScout',
            'spot' => $spots[$id],
        ]);
    }

    public function add(): void
    {
        $categories = ['Architecture', 'Industrial', 'Urban', 'Nature', 'Drone Friendly', 'Night'];

        $this->render('add-spot', [
            'pageTitle' => 'Add New Spot | SpotScout',
            'categories' => $categories,
        ]);
    }

    public function profile(): void
    {
        $user = [
            'name' => 'Adam Kowalski',
            'handle' => '@adam_cine',
            'role' => 'Photographer & Drone Operator',
            'location' => 'Wrocław, PL',
            'bio' => 'Searching for brutalism, fog, and golden hour light. Documenting industrial relics and mountain viewpoints across Central Europe.',
            'points' => 154,
            'addedCount' => 12,
            'savesCount' => '3.8k',
            'rank' => 'Top 5% Scout',
            'gear' => ['RED Komodo 6K', '35mm f/1.4 Prime', 'DJI Mavic 3 Pro'],
        ];

        $spots = $this->getSpotsData();

        $this->render('profile', [
            'pageTitle' => $user['name'] . ' (' . $user['handle'] . ') | SpotScout Profile',
            'user' => $user,
            'mySpots' => $spots,
        ]);
    }
}