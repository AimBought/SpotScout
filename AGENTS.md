# AGENTS.md

## Cel projektu
SpotScout – platforma dla fotografów i filmowców do odkrywania lokacji. Prosta aplikacja oparta na wzorcu MVC w PHP 8.3 uruchamiana przez Docker Compose.

## Struktura
- `public/index.php` – Front Controller, punkt wejścia aplikacji i rejestracja tras.
- `public/views/` – responsywne szablony widoków HTML (Mobile-First).
- `public/css/style.css` – główny arkusz stylów oparty na zmiennych CSS, Flexbox i Grid.
- `src/Routing.php` – dopasowanie tras i uruchamianie kontrolerów.
- `src/controllers/` – kontrolery w namespace `App\Controller` dziedziczące po `AppController`.

## Zasady pracy
- Zachowuj `declare(strict_types=1)` we wszystkich nowych plikach PHP.
- Używaj czystego PHP. Nie dodawaj zewnętrznych frameworków ani zależności bez wyraźnej zgody.
- Kontroler pobiera i przygotowuje dane, a widok odpowiada wyłącznie za warstwę prezentacji (HTML/CSS).
- Zmienne wyświetlane w szablonach HTML zawsze zabezpieczaj przez `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- Wprowadzaj małe, iteracyjne zmiany. Nie modyfikuj plików niezwiązanych z bieżącym zadaniem.

## Uruchamianie i weryfikacja
- Start środowiska: `docker compose up --build -d`
- Aplikacja dostępna pod adresem: `http://localhost:8080`
- Aktualizacja mapy klas: `docker compose exec php composer dump-autoload`
- Po dodaniu nowej trasy sprawdź poprawny adres docelowy oraz odpowiedź 404 dla nieistniejącego adresu.
- Przed zakończeniem pracy pokaż `git diff` i podsumuj zmienione pliki.

## Bezpieczeństwo
- Nie odczytuj ani nie commituj plików `.env` zawierających sekrety.
- Nie wykonuj destrukcyjnych poleceń w terminalu bez potwierdzenia przez użytkownika.
- Nie zmieniaj automatycznie wygenerowanych plików w katalogu `vendor/`.