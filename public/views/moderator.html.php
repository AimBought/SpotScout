<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="/css/style.css?v=spot-gallery-review-1">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .role-badge{display:inline-flex;align-items:center;border:1px solid color-mix(in srgb,var(--color-brand) 35%,var(--color-border));border-radius:var(--radius-pill);padding:.2rem .55rem;color:var(--color-brand);font-size:.75rem;font-weight:700;line-height:1.2}
        .admin-tabs{display:flex;gap:.5rem;margin-bottom:var(--space-2);border-bottom:1px solid var(--color-border)}
        .admin-tab{display:inline-flex;padding:.75rem 1rem;border-bottom:3px solid transparent;color:var(--color-muted);font-weight:700;text-decoration:none}
        .admin-tab:hover,.admin-tab[aria-current="page"]{color:var(--color-brand);border-bottom-color:var(--color-brand)}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <header class="feed-header">
        <div><span class="eyebrow-label">SPOTSCOUT</span><h1>Moderator Panel</h1><p class="text-muted">Twoje konto ma uprawnienia moderatora.</p></div>
        <span class="role-badge" aria-label="Rola konta: moderator">Moderator</span>
    </header>
    <nav class="admin-tabs" aria-label="Zakładki panelu moderatora">
        <a class="admin-tab" href="/moderator" <?= $activeTab === 'spots' ? 'aria-current="page"' : '' ?>>Oczekujące spoty (<?= count($pendingSpots) ?>)</a>
        <a class="admin-tab" href="/moderator/photos" <?= $activeTab === 'photos' ? 'aria-current="page"' : '' ?>>Oczekujące zdjęcia (<?= count($pendingImageContributions) ?>)</a>
    </nav>
    <?php if ($activeTab === 'spots'): ?>
        <section aria-labelledby="pending-spots-heading"><header class="feed-header"><div><h2 id="pending-spots-heading">Oczekujące spoty</h2><p class="text-muted">Sprawdź nowe zgłoszenia społeczności.</p></div></header><?php require __DIR__ . '/partials/pending-spots.html.php'; ?></section>
    <?php else: ?>
        <?php require __DIR__ . '/partials/pending-image-contributions.html.php'; ?>
    <?php endif; ?>
</main>
<script src="/js/theme.js" defer></script>
</body>
</html>
