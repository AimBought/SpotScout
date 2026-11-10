<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="/css/style.css?v=moderator-panel-1">
    <style>
        .moderator-placeholder{min-height:14rem;display:grid;place-items:center;text-align:center;border:1px dashed var(--color-border);border-radius:var(--radius);background:var(--color-surface);padding:var(--space-3)}
        .moderator-placeholder p{max-width:34rem;margin:0;color:var(--color-muted)}
        .role-badge{display:inline-flex;align-items:center;border:1px solid color-mix(in srgb,var(--color-brand) 35%,var(--color-border));border-radius:var(--radius-pill);padding:.2rem .55rem;color:var(--color-brand);font-size:.75rem;font-weight:700;line-height:1.2}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <header class="feed-header">
        <div><span class="eyebrow-label">SPOTSCOUT</span><h1>Moderator Panel</h1><p class="text-muted">Twoje konto ma uprawnienia moderatora.</p></div>
        <span class="role-badge" aria-label="Rola konta: moderator">Moderator</span>
    </header>
    <section class="moderator-placeholder" aria-label="Miejsce na przyszłe narzędzia moderatorskie">
        <div><h2>Panel moderatora</h2><p>W przyszłości pojawią się tutaj narzędzia do moderowania spotów i treści społeczności. Na razie nie ma dostępnych dodatkowych akcji.</p></div>
    </section>
</main>
<script src="/js/theme.js" defer></script>
</body>
</html>
