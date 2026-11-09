<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="/css/style.css?v=spot-details-4">
    <style>.spot-hero-banner.has-image{background-image:linear-gradient(90deg,rgba(15,23,42,.9),rgba(15,23,42,.35)),url('<?= htmlspecialchars($spot->getImagePath() ?? '', ENT_QUOTES, 'UTF-8') ?>');background-size:cover;background-position:center}.spot-hero-banner h1{margin-top:var(--space-1)}.spot-hero-meta{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem 1rem}.spot-author{color:#cbd5e1;display:inline-flex;align-items:center;gap:.5rem;line-height:1.4}.spot-created-at{white-space:nowrap;line-height:1.4}.spot-author-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;flex:0 0 32px}.spot-gallery{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:var(--space-2)}.spot-gallery img{width:100%;max-height:32rem;object-fit:contain;border-radius:var(--radius-sm);background:var(--color-background);cursor:zoom-in}.gallery-control{min-width:2.75rem}#spot-location-map{height:360px;border:1px solid var(--color-border);border-radius:var(--radius)}.spot-lightbox{width:100vw;height:100vh;max-width:none;max-height:none;padding:3rem;border:0;background:rgba(3,7,18,.96);color:#fff}.spot-lightbox::backdrop{background:rgba(3,7,18,.96)}.spot-lightbox img{display:block;width:100%;height:100%;object-fit:contain}.spot-lightbox-close{position:absolute;top:1rem;right:1rem;color:#fff;background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.4)}@media(max-width:600px){.spot-gallery{grid-template-columns:2.75rem minmax(0,1fr) 2.75rem;gap:.5rem}.spot-lightbox{padding:3.5rem .5rem .5rem}}</style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <article>
        <header class="spot-hero-banner<?= $spot->getImagePath() !== null ? ' has-image' : '' ?>">
            <?php if ($spot->getCategories() !== []): ?><ul class="tag-list"><?php foreach ($spot->getCategories() as $category): ?><li class="badge"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul><?php endif; ?>
            <h1><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="spot-hero-meta"><span class="spot-author"><img class="spot-author-avatar" src="<?= htmlspecialchars($spot->getAuthorAvatarPath() ?? '/images/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>" alt="" width="32" height="32"><span>Dodano przez <?= htmlspecialchars($spot->getAuthorName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></span></span><?php if ($spot->getCreatedAt() !== null): ?><time class="spot-created-at" datetime="<?= htmlspecialchars($spot->getCreatedAt()->format(DATE_ATOM), ENT_QUOTES, 'UTF-8') ?>">· <?= htmlspecialchars($spot->getCreatedAt()->format('d.m.Y'), ENT_QUOTES, 'UTF-8') ?></time><?php endif; ?></p>
        </header>
        <div class="details-layout"><div>
            <?php if ($currentUserId !== null): ?>
                <form class="spot-bookmark-form spot-bookmark-details" action="/spot/bookmark/<?= (int) $spot->getId() ?>" method="post">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_to" value="/spot/details/<?= (int) $spot->getId() ?>">
                    <button class="spot-bookmark-button<?= $isBookmarked ? ' is-bookmarked' : '' ?>" type="submit" aria-label="<?= $isBookmarked ? 'Remove bookmark' : 'Save spot' ?>: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>" aria-pressed="<?= $isBookmarked ? 'true' : 'false' ?>" title="<?= $isBookmarked ? 'Saved spot' : 'Save spot' ?>">
                        <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.75h12v16.5l-6-4-6 4z"/></svg>
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($currentUserId !== null && $spot->getUserId() === $currentUserId): ?>
                <form action="/spot/delete/<?= (int) $spot->getId() ?>" method="post" style="margin-bottom:var(--space-2)" onsubmit="return confirm('Czy na pewno chcesz usunąć ten spot? Tej operacji nie można cofnąć.');">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn btn-danger" type="submit">Usuń mój spot</button>
                </form>
            <?php endif; ?>
            <section class="content-card" aria-labelledby="spot-info-heading"><h2 id="spot-info-heading">Informacje o miejscu</h2>
                <p><?= htmlspecialchars($spot->getDescription() ?? 'Brak dodatkowego opisu tego miejsca.', ENT_QUOTES, 'UTF-8') ?></p>
                <dl class="info-grid">
                    <div class="info-tile"><dt>Kategorie</dt><dd><?= htmlspecialchars($spot->getCategory() ?? 'Nie podano', ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div class="info-tile"><dt>Współrzędne GPS</dt><dd><?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?><?= htmlspecialchars(number_format($spot->getLatitude(), 6, '.', '') . ', ' . number_format($spot->getLongitude(), 6, '.', ''), ENT_QUOTES, 'UTF-8') ?><?php else: ?>Nie podano<?php endif; ?></dd></div>
                    <div class="info-tile"><dt>Dodane przez</dt><dd><?= htmlspecialchars($spot->getAuthorName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
                <?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?><a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="https://www.openstreetmap.org/?mlat=<?= rawurlencode((string) $spot->getLatitude()) ?>&amp;mlon=<?= rawurlencode((string) $spot->getLongitude()) ?>#map=16/<?= rawurlencode((string) $spot->getLatitude()) ?>/<?= rawurlencode((string) $spot->getLongitude()) ?>">Otwórz w OpenStreetMap</a><?php endif; ?>
            </section>
            <?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?>
                <section class="content-card" aria-labelledby="spot-map-heading"><h2 id="spot-map-heading">Lokalizacja</h2><div id="spot-location-map" aria-label="Mapa lokalizacji spotu"></div></section>
            <?php endif; ?>
            <?php if ($images !== []): ?>
                <section class="content-card" aria-labelledby="gallery-heading">
                    <div class="form-row-between"><h2 id="gallery-heading">Zdjęcia miejsca</h2><span class="badge"><span id="gallery-current">1</span> / <?= count($images) ?></span></div>
                    <div class="spot-gallery">
                        <button type="button" class="btn btn-outline gallery-control" id="gallery-previous" aria-label="Poprzednie zdjęcie">←</button>
                        <img id="gallery-image" src="<?= htmlspecialchars($images[0], ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie miejsca: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="button" class="btn btn-outline gallery-control" id="gallery-next" aria-label="Następne zdjęcie">→</button>
                    </div>
                </section>
                <dialog class="spot-lightbox" id="spot-lightbox" aria-label="Powiększone zdjęcie"><button class="btn btn-outline spot-lightbox-close" type="button" id="spot-lightbox-close" aria-label="Zamknij zdjęcie">Zamknij ✕</button><img id="spot-lightbox-image" alt="Powiększone zdjęcie spotu"></dialog>
            <?php endif; ?>
            <a class="btn btn-outline" href="/explore">← Wróć do Explore</a>
        </div></div>
    </article>
</main>
<nav class="mobile-bottom-nav" aria-label="Nawigacja mobilna"><a href="/explore" class="mobile-nav-item">Explore</a><a href="/spot/add" class="mobile-nav-item mobile-nav-add">+ Dodaj</a><a href="/profile" class="mobile-nav-item">Profil</a></nav>
<footer class="page site-footer"><p>&copy; 2026 SpotScout.</p></footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/js/theme.js" defer></script>
<script>
<?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?>
const locationMap = L.map('spot-location-map').setView([<?= json_encode($spot->getLatitude(), JSON_THROW_ON_ERROR) ?>, <?= json_encode($spot->getLongitude(), JSON_THROW_ON_ERROR) ?>], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(locationMap);
L.marker([<?= json_encode($spot->getLatitude(), JSON_THROW_ON_ERROR) ?>, <?= json_encode($spot->getLongitude(), JSON_THROW_ON_ERROR) ?>]).addTo(locationMap).bindPopup(<?= json_encode($spot->getTitle(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>);
<?php endif; ?>
<?php if ($images !== []): ?>
const galleryImages = <?= json_encode($images, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;
let galleryIndex = 0;
const galleryImage = document.getElementById('gallery-image');
const lightbox = document.getElementById('spot-lightbox');
function showGalleryImage(index) { galleryIndex = (index + galleryImages.length) % galleryImages.length; galleryImage.src = galleryImages[galleryIndex]; document.getElementById('gallery-current').textContent = String(galleryIndex + 1); }
galleryImage.addEventListener('click', () => { document.getElementById('spot-lightbox-image').src = galleryImages[galleryIndex]; lightbox.showModal(); });
document.getElementById('spot-lightbox-close').addEventListener('click', () => lightbox.close());
lightbox.addEventListener('click', event => { if (event.target === lightbox) lightbox.close(); });
<?php endif; ?>
<?php if (count($images) > 1): ?>
document.getElementById('gallery-previous').addEventListener('click', () => showGalleryImage(galleryIndex - 1));
document.getElementById('gallery-next').addEventListener('click', () => showGalleryImage(galleryIndex + 1));
<?php endif; ?>
</script>
</body>
</html>
