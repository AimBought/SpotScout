<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>
        (function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLGPg8YJ04IY4YZ0WMx6Y0hSp4pMwDlkRzTFbGMPT4ec7Zua7+CQRkpw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/header.html.php'; ?>

    <main class="page auth-wrapper">
        <section class="auth-card" aria-labelledby="register-heading">
            <header class="auth-header">
                <h1 id="register-heading">Join SpotScout</h1>
                <p class="text-muted">Join thousands of filmmakers, photographers, and explorers worldwide.</p>
            </header>

            <form action="/register" method="post" class="form-stack">
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="username">Username</label>
                        <span class="form-hint">Unique Handle</span>
                    </div>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="@lens_explorer"
                        required
                    >
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="email">E-mail</label>
                        <span class="form-hint">Account Email</span>
                    </div>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="alex@cineframe.io"
                        required
                    >
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password">Password</label>
                        <span class="form-hint">Secured</span>
                    </div>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="At least 8 characters"
                        minlength="8"
                        required
                    >
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password-repeat">Repeat Password</label>
                        <span class="form-hint">Confirm</span>
                    </div>
                    <input
                        type="password"
                        id="password-repeat"
                        name="password_repeat"
                        class="form-input"
                        placeholder="Re-enter password"
                        minlength="8"
                        required
                    >
                </div>

                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the Community Scouting Guidelines &amp; Terms</span>
                </label>

                <button type="submit" class="btn btn-primary btn-block">Sign Up →</button>
            </form>

            <footer class="auth-footer">
                <p style="margin: 0;">Already have an account? <a href="/login"><strong>Log In</strong></a></p>
            </footer>
        </section>
    </main>

    <footer class="page site-footer">
        <p>&copy; 2026 SpotScout. Precision Location Scouting Tools.</p>
    </footer>
<script src="/js/theme.js" defer></script>
</body>
</html>
