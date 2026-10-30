<?php

declare(strict_types=1);

namespace App\Controller;

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
        $this->render('login', [
            'pageTitle' => 'Log In | SpotScout',
        ]);
    }

    /**
     * Wyświetla formularz rejestracji nowego konta.
     */
    public function register(): void
    {
        $this->render('register', [
            'pageTitle' => 'Join SpotScout | Create an Account',
        ]);
    }
}
