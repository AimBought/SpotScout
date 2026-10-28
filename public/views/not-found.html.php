<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
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
</body>
</html>
