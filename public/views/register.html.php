<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="/css/style.css?v=split-screen-3">
</head>
<body class="split-screen-body">
    <?php require __DIR__ . '/partials/header.html.php'; ?>
    <main class="split-screen">
        <section class="split-content">
            <div class="split-main split-auth-main">
                <section aria-labelledby="register-heading">
                    <header class="auth-header">
                        <h1 id="register-heading">Create your SpotScout account</h1>
                        <p class="text-muted">Join a community finding and sharing remarkable filming locations.</p>
                    </header>

                    <?php if ($errors !== []): ?>
                        <ul class="form-errors" role="alert">
                            <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <form action="/register" method="post" class="form-stack">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label for="username" class="form-label-row">Username</label>
                            <input type="text" id="username" name="username" class="form-input" placeholder="@lens_explorer" autocomplete="username" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label-row">E-mail</label>
                            <input type="email" id="email" name="email" class="form-input" placeholder="you@example.com" autocomplete="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label-row">Password</label>
                            <input type="password" id="password" name="password" class="form-input" placeholder="At least 8 characters" minlength="8" autocomplete="new-password" required>
                        </div>
                        <div class="form-group">
                            <label for="password-repeat" class="form-label-row">Repeat password</label>
                            <input type="password" id="password-repeat" name="password_repeat" class="form-input" placeholder="Confirm your password" minlength="8" autocomplete="new-password" required>
                        </div>
                        <label class="checkbox-label"><input type="checkbox" name="terms" required><span>I agree to the Community Scouting Guidelines &amp; Terms</span></label>
                        <button type="submit" class="btn btn-primary btn-block">Create account <span aria-hidden="true">→</span></button>
                    </form>
                    <footer class="auth-footer"><p style="margin:0">Already have an account? <a href="/login"><strong>Log in</strong></a></p></footer>
                </section>
            </div>
            <footer class="split-footer"><a href="/">← Back to SpotScout</a></footer>
        </section>
        <?php require __DIR__ . '/partials/featured-spot-panel.html.php'; ?>
    </main>
    <script src="/js/theme.js" defer></script>
</body>
</html>
