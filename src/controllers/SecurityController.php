<?php

declare(strict_types=1);

namespace App\Controller;

use App\Database;
use App\Repository\UserRepository;
use PDOException;

/**
 * Kontroler odpowiedzialny za widoki uwierzytelniania:
 * logowanie oraz rejestrację użytkownika.
 */
final class SecurityController extends AppController
{
    /**
     * Wyświetla formularz logowania.
     */
    public function login(): void
    {
        $errors = [];
        $csrfError = false;

        if ($this->isPost()) {
            $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
            $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

            if (!$this->isCsrfTokenValid($token)) {
                $csrfError = true;
                $errors[] = 'Formularz wygasł lub token bezpieczeństwa jest nieprawidłowy. Spróbuj ponownie.';
            } else {
                $user = $this->users()->findByEmail($email);

                if ($user === null || !password_verify($password, $user['password_hash'])) {
                    $errors[] = 'Nieprawidłowy adres e-mail lub hasło.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    unset($_SESSION['csrf_token']);
                    $this->csrfToken();
                    $this->redirect('/explore');
                }
            }
        }

        if ($errors !== []) {
            http_response_code($csrfError ? 403 : 401);
        }

        $this->render('login', [
            'pageTitle' => 'Log In | SpotScout',
            'errors' => $errors,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    /**
     * Wyświetla formularz rejestracji nowego konta.
     */
    public function register(): void
    {
        $errors = [];
        $csrfError = false;

        if ($this->isPost()) {
            $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
            $name = trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : '');
            $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            $passwordRepeat = is_string($_POST['password_repeat'] ?? null) ? $_POST['password_repeat'] : '';

            if (!$this->isCsrfTokenValid($token)) {
                $csrfError = true;
                $errors[] = 'Formularz wygasł lub token bezpieczeństwa jest nieprawidłowy. Spróbuj ponownie.';
            } else {
                $nameLength = preg_match_all('/./us', $name);
                if ($name === '' || $nameLength === false || $nameLength > 100) {
                    $errors[] = 'Nazwa musi mieć od 1 do 100 znaków.';
                }
                $emailLength = preg_match_all('/./us', $email);
                if ($emailLength === false || $emailLength > 100) {
                    $errors[] = 'Adres e-mail nie może przekraczać 100 znaków.';
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Podaj prawidłowy adres e-mail (maksymalnie 100 znaków).';
                }
                if (strlen($password) < 8) {
                    $errors[] = 'Hasło musi mieć co najmniej 8 znaków.';
                }
                if ($password !== $passwordRepeat) {
                    $errors[] = 'Podane hasła nie są zgodne.';
                }

                if ($errors === []) {
                    try {
                        $this->users()->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
                        $this->redirect('/login');
                    } catch (PDOException) {
                        $errors[] = 'Konto z tym adresem e-mail już istnieje.';
                    }
                }
            }
        }

        if ($errors !== []) {
            http_response_code($csrfError ? 403 : 400);
        }

        $this->render('register', [
            'pageTitle' => 'Join SpotScout | Create an Account',
            'errors' => $errors,
            'csrfToken' => $this->csrfToken(),
        ]);
    }

    public function logout(): void
    {
        $token = is_string($_POST['_csrf_token'] ?? null) ? $_POST['_csrf_token'] : '';
        if (!$this->isPost() || !$this->isCsrfTokenValid($token)) {
            http_response_code(400);
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
        $this->redirect('/');
    }

    private function users(): UserRepository
    {
        return new UserRepository((new Database())->connect());
    }
}
