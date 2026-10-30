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
        <section class="auth-card" aria-labelledby="login-heading">
            <header class="auth-header">
                <h1 id="login-heading">Welcome Back</h1>
                <p class="text-muted">Sign in to access your saved spots, coordinates, and scouting notes.</p>
            </header>

            <?php if (($_GET['registered'] ?? '') === '1' && ($_GET['mail'] ?? '') === 'sent'): ?>
                <p class="form-success" role="status">Konto zostało utworzone. E-mail powitalny wysłany!</p>
            <?php elseif (($_GET['registered'] ?? '') === '1' && ($_GET['mail'] ?? '') === 'failed'): ?>
                <p class="form-warning" role="status">Konto utworzone, ale wystąpił błąd poczty.</p>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <ul class="form-errors" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form action="/login" method="post" class="form-stack">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="email">Username or E-mail</label>
                        <span class="form-hint">Scout ID</span>
                    </div>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="e.g. pilot@spotscout.io"
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
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="form-row-between">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Log In →</button>
            </form>

            <footer class="auth-footer">
                <p style="margin: 0;">Don't have an account? <a href="/register"><strong>Sign Up</strong></a></p>
            </footer>
        </section>
    </main>

    <footer class="page site-footer">
        <p>&copy; 2026 SpotScout. Precision Location Scouting Tools.</p>
    </footer>
<script src="/js/theme.js" defer></script>
</body>
</html>
