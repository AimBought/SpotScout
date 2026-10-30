<?php

declare(strict_types=1);

namespace App\Controller;

/**
 * Bazowy kontroler aplikacji SpotScout.
 *
 * Dostarcza wspólne metody pomocnicze (np. renderowanie widoków)
 * dla wszystkich kontrolerów dziedziczących po tej klasie.
 */
abstract class AppController
{
    /**
     * Renderuje szablon widoku, przekazując do niego zmienne.
     *
     * Metoda buduje ścieżkę do pliku `.html.php` w katalogu `public/views/`,
     * rozpakowuje tablicę zmiennych do lokalnego scope'u i dołącza szablon
     * za pomocą `require`.
     *
     * @param string               $template  Nazwa szablonu (bez rozszerzenia), np. 'home'
     * @param array<string, mixed> $variables Tablica zmiennych przekazywanych do widoku
     *
     * @throws \RuntimeException Gdy plik szablonu nie istnieje
     */
    protected function render(string $template, array $variables = []): void
    {
        $templatePath = dirname(__DIR__, 2) . '/public/views/' . $template . '.html.php';

        if (!is_file($templatePath)) {
            throw new \RuntimeException('View not found: ' . $template);
        }

        extract($variables, EXTR_SKIP);
        require $templatePath;
    }
}
