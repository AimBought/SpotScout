<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="/css/style.css?v=admin-roles-2">
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
        .moderator-placeholder{min-height:14rem;display:grid;place-items:center;text-align:center;padding:var(--space-3)}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <nav class="admin-tabs" aria-label="Zakładki panelu administracyjnego">
        <a class="admin-tab" href="/admin" <?= $activeTab === 'users' ? 'aria-current="page"' : '' ?>>Użytkownicy</a>
        <a class="admin-tab" href="/admin/moderator" <?= $activeTab === 'moderator' ? 'aria-current="page"' : '' ?>>Moderator Panel</a>
    </nav>
    <?php if ($activeTab === 'moderator'): ?>
        <section class="content-card moderator-placeholder"><div><h1>Panel moderatora</h1><p class="text-muted">W przyszłości pojawią się tutaj narzędzia do moderowania spotów i treści społeczności.</p></div></section>
    <?php else: ?>
    <header class="feed-header"><div><span class="eyebrow-label">SPOTSCOUT</span><h1>Admin Panel</h1><p class="text-muted">Zarządzaj rolami użytkowników.</p></div></header>
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
