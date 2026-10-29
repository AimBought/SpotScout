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
        <section class="auth-card auth-header" aria-labelledby="error-heading">
            <span class="radar-status">● ERROR 404</span>
            <h1 id="error-heading" style="margin-top: 0.5rem;">Location Not Found</h1>
            <p class="text-muted">
                <?= htmlspecialchars($errorMessage ?? 'Szukana strona lub lokacja nie istnieje w bazie SpotScout.', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <div class="hero-actions" style="justify-content: center;">
                <a href="/explore" class="btn btn-primary">Go to Explore Feed</a>
                <a href="/" class="btn btn-outline">Welcome Screen</a>
            </div>
        </section>
    </main>
<script src="/js/theme.js" defer></script>
</body>
</html>
