<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page forbidden-page">
    <section class="content-card forbidden-card" role="alert" aria-labelledby="forbidden-heading">
        <span class="forbidden-icon" aria-hidden="true">403</span>
        <h1 id="forbidden-heading">Nie masz dostępu do tej strony</h1>
        <p class="text-muted">Ta sekcja jest dostępna tylko dla moderatorów i administratorów.</p>
        <div class="form-actions">
            <a class="btn btn-primary" href="/explore">Wróć do Explore</a>
            <a class="btn btn-outline" href="/profile">Przejdź do profilu</a>
        </div>
    </section>
</main>
<script src="/js/theme.js" defer></script>
</body>
</html>
