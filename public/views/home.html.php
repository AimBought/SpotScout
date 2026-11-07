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
            <div class="split-main">
                <span class="radar-status">THE WORLD IS YOUR SET</span>
                <h1>Discover &amp; Scout Breathtaking Cine Locations</h1>
                <p class="split-description">Find remarkable places for your next shoot, explore real locations, and share the spots worth remembering with a creative community.</p>
                <div class="split-actions">
                    <a class="btn btn-primary" href="/login">Log In</a>
                    <a class="btn btn-outline" href="/register">Sign Up</a>
                </div>
            </div>
            <footer class="split-footer">Discover places. Create something unforgettable.</footer>
        </section>
        <?php require __DIR__ . '/partials/featured-spot-panel.html.php'; ?>
    </main>
    <script src="/js/theme.js" defer></script>
</body>
</html>
