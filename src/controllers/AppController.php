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
    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    protected function requireLogin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    protected function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    protected function isCsrfTokenValid(string $token): bool
    {
        return isset($_SESSION['csrf_token'])
            && is_string($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

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
