<?php

declare(strict_types=1);

namespace App;

use App\Controller\AppController;

/**
 * Prosty router HTTP oparty na wyrażeniach regularnych.
 *
 * Rejestruje trasy (GET / POST) przypisane do kontrolerów i akcji,
 * a następnie dopasowuje przychodzące żądanie do zarejestrowanej trasy
 * i uruchamia odpowiedni kontroler.
 */
final class Routing
{
    /** @var array<int, array{method: string, pattern: string, controller: string|AppController, action: string}> */
    private array $routes = [];

    /**
     * Rejestruje trasę obsługującą żądania GET.
     *
     * @param string $path       Wzorzec ścieżki, np. '/spot/details/{id}'
     * @param string|AppController $controller FQCN lub instancja kontrolera
     * @param string $action     Nazwa metody akcji do wywołania
     */
    public function get(string $path, string|AppController $controller, string $action): void
    {
        $this->add('GET', $path, $controller, $action);
    }

    /**
     * Rejestruje trasę obsługującą żądania POST.
     *
     * @param string $path       Wzorzec ścieżki
     * @param string|AppController $controller FQCN lub instancja kontrolera
     * @param string $action     Nazwa metody akcji do wywołania
     */
    public function post(string $path, string|AppController $controller, string $action): void
    {
        $this->add('POST', $path, $controller, $action);
    }

    /**
     * Dodaje trasę do wewnętrznej tablicy routingu.
     *
     * @param string $method     Metoda HTTP (GET, POST)
     * @param string $path       Wzorzec ścieżki URL
     * @param string|AppController $controller FQCN lub instancja kontrolera
     * @param string $action     Nazwa metody akcji
     */
    private function add(
        string $method,
        string $path,
        string|AppController $controller,
        string $action,
    ): void {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $this->createPattern($path),
            'controller' => $controller,
            'action' => $action,
        ];
    }

    /**
     * Kompiluje wzorzec ścieżki do wyrażenia regularnego.
     *
     * Segmenty w nawiasach klamrowych (np. `{id}`) zamieniane są
     * na nazwane grupy przechwytujące dopasowujące cyfry (`\d+`).
     *
     * @param string $path Wzorzec ścieżki, np. '/spot/details/{id}'
     *
     * @return string Skompilowane wyrażenie regularne
     */
    private function createPattern(string $path): string
    {
        $parts = explode('/', $path);

        foreach ($parts as &$part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $parameterName = trim($part, '{}');
                $part = '(?P<' . $parameterName . '>\d+)';
                continue;
            }

            $part = preg_quote($part, '~');
        }
        unset($part);

        return '~^' . implode('/', $parts) . '$~';
    }

    /**
     * Dopasowuje bieżące żądanie HTTP do zarejestrowanych tras i uruchamia kontroler.
     *
     * Iteruje po tablicy tras, porównuje metodę HTTP i wzorzec URL.
     * Po znalezieniu dopasowania tworzy instancję kontrolera, weryfikuje
     * dziedziczenie po {@see AppController} i wywołuje akcję z parametrami.
     *
     * @param string $method Metoda HTTP żądania (np. 'GET', 'POST')
     * @param string $uri    Pełny URI żądania (np. '/spot/details/3?ref=map')
     *
     * @return bool true jeśli trasa została dopasowana i obsłużona, false w przeciwnym razie
     *
     * @throws \RuntimeException Gdy kontroler nie dziedziczy po AppController
     */
    public function dispatch(string $method, string $uri): bool
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            $parameters = array_filter(
                $matches,
                static fn (int|string $key): bool => is_string($key),
                ARRAY_FILTER_USE_KEY,
            );

            $parameters = array_map(
                static fn (string $value): int|string =>
                    ctype_digit($value) ? (int) $value : $value,
                $parameters,
            );

            $controllerTarget = $route['controller'];
            $controller = is_string($controllerTarget) ? new $controllerTarget() : $controllerTarget;

            if (!$controller instanceof AppController) {
                throw new \RuntimeException('Invalid controller');
            }

            $controller->{$route['action']}(...$parameters);

            return true;
        }

        return false;
    }
}
