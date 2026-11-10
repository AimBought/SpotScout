<?php

declare(strict_types=1);

namespace App\Controller;

final class ModeratorController extends AppController
{
    public function index(): void
    {
        $this->requireRole(['moderator', 'admin']);
        $this->render('moderator', [
            'pageTitle' => 'Moderator Panel | SpotScout',
        ]);
    }
}
