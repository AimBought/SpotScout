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
        .admin-table-wrap{overflow-x:auto}
        .admin-table{width:100%;border-collapse:collapse;min-width:38rem}
        .admin-table th,.admin-table td{padding:.85rem 1rem;border-bottom:1px solid var(--color-border);text-align:left;vertical-align:middle}
        .admin-table th{color:var(--color-muted);font-size:.82rem;text-transform:uppercase;letter-spacing:.04em}
        .admin-role-form{display:flex;align-items:center;gap:.5rem;margin:0}
        .admin-role-form select{min-width:9rem}
        .admin-tabs{display:flex;gap:.5rem;margin-bottom:var(--space-2);border-bottom:1px solid var(--color-border)}
        .admin-tab{display:inline-flex;padding:.75rem 1rem;border-bottom:3px solid transparent;color:var(--color-muted);font-weight:700;text-decoration:none}
        .admin-tab:hover,.admin-tab[aria-current="page"]{color:var(--color-brand);border-bottom-color:var(--color-brand)}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <header class="feed-header"><div><span class="eyebrow-label">SPOTSCOUT</span><h1>Admin Panel</h1></div></header>
    <nav class="admin-tabs" aria-label="Zakładki panelu administracyjnego">
        <a class="admin-tab" href="/admin" <?= $activeTab === 'users' ? 'aria-current="page"' : '' ?>>Użytkownicy</a>
        <a class="admin-tab" href="/admin/moderator" <?= $activeTab === 'spots' ? 'aria-current="page"' : '' ?>>Oczekujące spoty (<?= count($pendingSpots) ?>)</a>
        <a class="admin-tab" href="/admin/photos" <?= $activeTab === 'photos' ? 'aria-current="page"' : '' ?>>Oczekujące zdjęcia (<?= count($pendingImageContributions) ?>)</a>
    </nav>
    <?php if ($activeTab === 'spots'): ?>
        <section aria-labelledby="pending-spots-heading"><header class="feed-header"><div><h2 id="pending-spots-heading">Oczekujące spoty</h2><p class="text-muted">Sprawdź nowe zgłoszenia społeczności.</p></div></header><?php require __DIR__ . '/partials/pending-spots.html.php'; ?></section>
    <?php elseif ($activeTab === 'photos'): ?>
        <?php require __DIR__ . '/partials/pending-image-contributions.html.php'; ?>
    <?php else: ?>
        <p class="text-muted">Zarządzaj rolami użytkowników.</p>
        <section class="content-card admin-table-wrap" aria-label="Użytkownicy">
            <table class="admin-table">
                <thead><tr><th>Użytkownik</th><th>E-mail</th><th>Rola</th><th>Akcja</th></tr></thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user->getName(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user->getEmail(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user->getRole(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <form class="admin-role-form" action="/admin/role" method="post">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="user_id" value="<?= $user->getId() ?>">
                                <label class="sr-only" for="role-<?= $user->getId() ?>">Rola użytkownika <?= htmlspecialchars($user->getName(), ENT_QUOTES, 'UTF-8') ?></label>
                                <select id="role-<?= $user->getId() ?>" name="role">
                                    <?php foreach (['user', 'moderator', 'admin'] as $role): ?>
                                        <option value="<?= $role ?>" <?= $user->getRole() === $role ? 'selected' : '' ?>><?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline" type="submit">Zapisz</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endif; ?>
</main>
<script src="/js/theme.js" defer></script>
</body>
</html>
