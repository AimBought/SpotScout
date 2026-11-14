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
    <link rel="stylesheet" href="/css/style.css?v=full-notifications-1">
    <style>
        .notifications-page { max-width: 54rem; margin-inline: auto; }
        .notifications-heading { display: flex; align-items: end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: var(--space-3); }
        .notifications-list { display: grid; gap: .8rem; }
        .notification-full-item { padding: 1.1rem 1.25rem; border: 1px solid var(--color-border); border-radius: var(--radius); background: var(--color-surface); box-shadow: var(--shadow-card); overflow-wrap: anywhere; }
        .notification-full-item.is-unread { border-left: 4px solid var(--color-brand); }
        .notification-full-message { margin: 0; white-space: pre-wrap; line-height: 1.65; }
        .notification-full-meta { display: flex; justify-content: space-between; gap: .75rem; align-items: center; margin-top: .8rem; color: var(--color-muted); font-size: .85rem; }
        .notification-read-state { color: var(--color-brand); font-size: .75rem; font-weight: 700; text-transform: uppercase; }
        .notifications-actions { display: flex; align-items: center; gap: .75rem; }
        .notifications-empty { padding: 1.25rem; border: 1px solid var(--color-border); border-radius: var(--radius); background: var(--color-surface); color: var(--color-muted); }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page notifications-page">
    <header class="notifications-heading">
        <div><span class="eyebrow-label">SPOTSCOUT</span><h1>Powiadomienia</h1><p class="text-muted">Pełna historia wiadomości dotyczących Twojego konta i spotów.</p></div>
        <div class="notifications-actions">
            <a class="btn btn-outline" href="/explore">Wróć do Explore</a>
            <?php $unreadOnPage = array_values(array_filter($notifications, static fn ($notification): bool => !$notification->isRead())); ?>
            <?php if ($unreadOnPage !== []): ?>
                <form action="/notifications/read" method="post">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_to" value="/notifications">
                    <?php foreach ($unreadOnPage as $notification): ?><input type="hidden" name="notification_ids[]" value="<?= $notification->getId() ?>"><?php endforeach; ?>
                    <button class="btn btn-primary" type="submit">Oznacz wszystkie jako przeczytane</button>
                </form>
            <?php endif; ?>
        </div>
    </header>
    <section class="notifications-list" aria-label="Lista powiadomień">
        <?php if ($notifications === []): ?>
            <p class="notifications-empty">Nie masz jeszcze żadnych powiadomień.</p>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <article class="notification-full-item<?= $notification->isRead() ? '' : ' is-unread' ?>">
                    <p class="notification-full-message"><?= htmlspecialchars($notification->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="notification-full-meta">
                        <time datetime="<?= htmlspecialchars($notification->getCreatedAt()->format(DATE_ATOM), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($notification->getCreatedAt()->format('d.m.Y H:i'), ENT_QUOTES, 'UTF-8') ?></time>
                        <span class="notification-read-state"><?= $notification->isRead() ? 'Przeczytane' : 'Nowe' ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
<script src="/js/theme.js" defer></script>
</body>
</html>
