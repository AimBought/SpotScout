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
    <link rel="stylesheet" href="/css/style.css?v=public-profiles-1">
    <style>
        .public-profile-header{display:grid;grid-template-columns:auto minmax(0,1fr);align-items:center;gap:1.25rem;padding:clamp(1.25rem,3vw,2rem);margin-block:1.5rem;background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-card)}
        .public-profile-avatar{width:clamp(5rem,12vw,8rem);height:clamp(5rem,12vw,8rem);object-fit:cover;border:3px solid var(--color-border);border-radius:50%}
        .public-profile-header-content{min-width:0}
        .public-profile-header h1{margin:0 0 .75rem}
        .public-profile-stats{display:flex;flex-wrap:wrap;gap:.65rem;margin-bottom:.8rem}
        .public-profile-stat{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .8rem;border:1px solid var(--color-border);border-radius:var(--radius-pill);background:var(--color-background);color:var(--color-muted);font-size:.92rem}
        .public-profile-stat svg{width:1.1rem;height:1.1rem;flex:none;color:var(--color-brand)}
        .public-profile-stat strong{color:var(--color-text);font-size:1rem}
        .public-profile-bio{margin:.85rem 0 0;line-height:1.65;white-space:pre-wrap;overflow-wrap:anywhere}
        .public-profile-spots{margin-block:2rem}
        .public-profile-spots h2{margin-bottom:1rem}
        .public-profile-spots .spot-card-visual.has-image{background-size:contain;background-repeat:no-repeat;background-position:center;background-color:var(--color-background);min-height:15rem}
        .public-profile-upvotes{margin:.35rem 0 0;color:var(--color-muted);font-size:.9rem}
        .public-profile-empty{padding:1.25rem;border:1px solid var(--color-border);border-radius:var(--radius);background:var(--color-surface);color:var(--color-muted)}
        @media(max-width:34rem){.public-profile-header{grid-template-columns:1fr;text-align:center}.public-profile-avatar{margin-inline:auto}.public-profile-stats{justify-content:center}}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <section class="public-profile-header" aria-labelledby="public-profile-name">
        <img class="public-profile-avatar" src="<?= htmlspecialchars($user->getAvatarPath() ?? '/images/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>" alt="Awatar użytkownika <?= htmlspecialchars($user->getName(), ENT_QUOTES, 'UTF-8') ?>" width="128" height="128">
        <div class="public-profile-header-content">
            <h1 id="public-profile-name"><?= htmlspecialchars($user->getName(), ENT_QUOTES, 'UTF-8') ?></h1>
            <div class="public-profile-stats" aria-label="Statystyki użytkownika">
                <span class="public-profile-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg><strong><?= count($spots) ?></strong> opublikowane spoty</span>
                <span class="public-profile-stat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 10v10H4V10h3Zm3 10h7.2a2 2 0 0 0 1.96-1.61l1.2-6A2 2 0 0 0 18.4 10H14l.65-3.24A2.3 2.3 0 0 0 12.4 4L10 10v10Z"/></svg><strong><?= array_sum(array_map(static fn ($spot): int => $spot->getUpvoteCount(), $spots)) ?></strong> upvotów</span>
            </div>
            <?php if ($user->getBio() !== null && trim($user->getBio()) !== ''): ?>
                <p class="public-profile-bio"><?= htmlspecialchars($user->getBio(), ENT_QUOTES, 'UTF-8') ?></p>
            <?php else: ?>
                <p class="public-profile-bio text-muted">Ten użytkownik nie dodał jeszcze opisu.</p>
            <?php endif; ?>
        </div>
    </section>
    <section class="public-profile-spots" aria-labelledby="public-profile-spots-heading">
        <h2 id="public-profile-spots-heading">Opublikowane spoty</h2>
        <?php if ($spots === []): ?><p class="public-profile-empty">Ten użytkownik nie ma jeszcze opublikowanych spotów.</p><?php endif; ?>
        <div class="spots-grid profile-spots">
            <?php foreach ($spots as $spot): ?>
                <?php $imagePath = $spot->getPreviewImagePath() ?? '/images/featured-spot.svg'; ?>
                <article class="spot-card">
                    <a href="/spot/details/<?= (int) $spot->getId() ?>" class="spot-card-link">
                        <div class="spot-card-visual has-image" style="background-image:url('<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>')">
                            <?php if ($spot->getCategory() !== null): ?><ul class="tag-list"><li class="badge"><?= htmlspecialchars($spot->getCategory(), ENT_QUOTES, 'UTF-8') ?></li></ul><?php endif; ?>
                        </div>
                        <div class="spot-card-body">
                            <h3 class="spot-title-link"><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="spot-meta"><?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?><?= htmlspecialchars(number_format($spot->getLatitude(), 5, '.', '') . ', ' . number_format($spot->getLongitude(), 5, '.', ''), ENT_QUOTES, 'UTF-8') ?><?php else: ?>Brak współrzędnych<?php endif; ?></p>
                            <footer class="spot-card-footer"><span class="light-tag">Szczegóły i informacje</span><span class="details-link">👍 <?= $spot->getUpvoteCount() ?> · Zobacz spot →</span></footer>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<footer class="page site-footer"><p><a href="/explore">Wróć do Explore</a></p></footer>
<script src="/js/theme.js" defer></script>
</body>
</html>
