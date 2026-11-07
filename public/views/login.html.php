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
                <section aria-labelledby="login-heading">
                    <header class="auth-header">
                        <h1 id="login-heading">Welcome back</h1>
                        <p class="text-muted">Sign in to access your saved spots and scouting notes.</p>
                    </header>

                    <?php if (($_GET['registered'] ?? '') === '1' && ($_GET['mail'] ?? '') === 'sent'): ?>
                        <p class="form-success" role="status">Your account has been created. A welcome email is on its way.</p>
                    <?php elseif (($_GET['registered'] ?? '') === '1' && ($_GET['mail'] ?? '') === 'failed'): ?>
                        <p class="form-warning" role="status">Your account was created, but the welcome email could not be sent.</p>
                    <?php endif; ?>

                    <?php if ($errors !== []): ?>
                        <ul class="form-errors" role="alert">
                            <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <form action="/login" method="post" class="form-stack">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-group">
                            <label for="email" class="form-label-row">Username or e-mail</label>
                            <input type="text" id="email" name="email" class="form-input" placeholder="you@example.com" autocomplete="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label-row">Password</label>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" autocomplete="current-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Log In <span aria-hidden="true">→</span></button>
                    </form>
                    <footer class="auth-footer"><p style="margin:0">Don't have an account? <a href="/register"><strong>Sign up</strong></a></p></footer>
                </section>
            </div>
            <footer class="split-footer"><a href="/">← Back to SpotScout</a></footer>
        </section>
        <?php require __DIR__ . '/partials/featured-spot-panel.html.php'; ?>
    </main>
    <script src="/js/theme.js" defer></script>
</body>
</html>
