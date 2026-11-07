<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="/css/style.css">
    <style>
        #explore-map{height:380px;border:1px solid var(--color-border);border-radius:var(--radius);margin-block:var(--space-2)}
        .spot-card-link{display:flex;flex-direction:column;height:100%;color:inherit}
        .spot-card-link:hover{color:inherit}
        .spot-card-visual.has-image{background-size:cover;background-position:center;min-height:13rem}
        .empty-spots{padding:var(--space-3);background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);color:var(--color-muted)}
        .leaflet-popup-content a{color:var(--color-brand);font-weight:700}
        .spot-author-avatar{width:24px;height:24px;border-radius:50%;object-fit:cover;vertical-align:middle;margin-right:.4rem}
        @media (min-width:48rem){.explore-layout{grid-template-columns:minmax(0,1fr)}.feed-column{min-width:0}.spots-grid{grid-template-columns:repeat(auto-fill,minmax(18rem,1fr))}#explore-map{height:min(52vh,520px)}}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page explore-layout">
    <section class="feed-column" aria-labelledby="explore-heading">
        <header class="feed-header">
            <div><span class="eyebrow-label">SPOTSCOUT STUDIO</span><h1 id="explore-heading">Explore locations</h1><p class="text-muted">Odkrywaj miejsca polecane przez społeczność.</p></div>
            <a class="btn btn-primary" href="/spot/add">+ Dodaj spot</a>
        </header>
        <section class="radar-card" aria-labelledby="map-heading">
            <div class="radar-top"><div><span class="radar-status">● MAPA SPOTÓW</span><h2 class="radar-title" id="map-heading">Znajdź lokalizację</h2></div></div>
            <div id="explore-map" aria-label="Mapa spotów"></div>
        </section>
        <section class="spots-grid" aria-label="Lista spotów">
            <?php if ($spots === []): ?><p class="empty-spots">Nie dodano jeszcze żadnych spotów. Dodaj pierwszy i podziel się lokalizacją.</p><?php endif; ?>
            <?php foreach ($spots as $spot): ?>
                <?php $imagePath = $spot->getImagePath(); ?>
                <article class="spot-card">
                    <a class="spot-card-link" href="/spot/details/<?= (int) $spot->getId() ?>">
                        <div class="spot-card-visual<?= $imagePath !== null ? ' has-image' : '' ?>"<?php if ($imagePath !== null): ?> style="background-image: url('<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>')"<?php endif; ?>>
                            <?php if ($spot->getCategory() !== null): ?><ul class="tag-list"><li class="badge"><?= htmlspecialchars($spot->getCategory(), ENT_QUOTES, 'UTF-8') ?></li></ul><?php endif; ?>
                            <div class="visual-bottom"><span class="photo-author"><img class="spot-author-avatar" src="<?= htmlspecialchars($spot->getAuthorAvatarPath() ?? '/images/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>" alt="" width="24" height="24">Dodano przez <?= htmlspecialchars($spot->getAuthorName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></span></div>
                        </div>
                        <div class="spot-card-body"><div class="spot-card-title-row"><h2 class="spot-title-link"><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h2></div>
                            <p class="spot-meta"><?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?><?= htmlspecialchars(number_format($spot->getLatitude(), 5, '.', '') . ', ' . number_format($spot->getLongitude(), 5, '.', ''), ENT_QUOTES, 'UTF-8') ?><?php else: ?>Brak współrzędnych<?php endif; ?></p>
                            <footer class="spot-card-footer"><span class="light-tag">Szczegóły i informacje</span><span class="details-link">Zobacz spot →</span></footer>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
</main>
<nav class="mobile-bottom-nav" aria-label="Nawigacja mobilna"><a href="/explore" class="mobile-nav-item is-active">Explore</a><a href="/spot/add" class="mobile-nav-item mobile-nav-add">+ Dodaj</a><a href="/profile" class="mobile-nav-item">Profil</a></nav>
<footer class="page site-footer"><p>&copy; 2026 SpotScout.</p></footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/js/theme.js" defer></script>
<script>
const spots = <?= json_encode(array_map(static fn ($spot): array => ['id' => $spot->getId(), 'title' => $spot->getTitle(), 'latitude' => $spot->getLatitude(), 'longitude' => $spot->getLongitude()], array_filter($spots, static fn ($spot): bool => $spot->getLatitude() !== null && $spot->getLongitude() !== null)), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;
const map = L.map('explore-map').setView([52.1, 19.4], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
spots.forEach(spot => { const popup = document.createElement('a'); popup.href = `/spot/details/${spot.id}`; popup.textContent = spot.title; L.marker([spot.latitude, spot.longitude]).addTo(map).bindPopup(popup); });
if (spots.length) map.fitBounds(spots.map(spot => [spot.latitude, spot.longitude]), {padding: [24, 24]});
</script>
</body>
</html>
