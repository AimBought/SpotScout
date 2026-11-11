<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="/css/style.css?v=spot-gallery-review-1">
    <style>.spot-hero-banner.has-image{background-image:linear-gradient(90deg,rgba(15,23,42,.9),rgba(15,23,42,.35)),url('<?= htmlspecialchars($spot->getPreviewImagePath() ?? '/images/featured-spot.svg', ENT_QUOTES, 'UTF-8') ?>');background-size:cover;background-position:center}.spot-hero-banner h1{margin-top:var(--space-1)}.spot-hero-meta{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem 1rem}.spot-author{color:#cbd5e1;display:inline-flex;align-items:center;gap:.5rem;line-height:1.4}.spot-created-at{white-space:nowrap;line-height:1.4}.spot-author-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;flex:0 0 32px}.spot-gallery{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:var(--space-2)}.spot-gallery img{width:100%;max-height:32rem;object-fit:contain;border-radius:var(--radius-sm);background:var(--color-background);cursor:zoom-in}.gallery-control{min-width:2.75rem}.spot-gallery-delete-form{position:absolute;top:.75rem;right:4.75rem;margin:0;z-index:1}.spot-gallery-delete{width:2.75rem;height:2.75rem;display:grid;place-items:center;border:1px solid #fda29b;border-radius:50%;background:#fff1f0;color:#b42318;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.22)}.spot-gallery-delete:hover{background:#fee4e2}.gallery-contributor{margin:.6rem 0 0;color:var(--color-muted);font-size:.9rem}.gallery-image-status:empty{display:none}.own-pending-photo-list{display:grid;gap:.65rem}.own-pending-photo-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.65rem .8rem;border:1px solid var(--color-border);border-radius:var(--radius-sm)}.own-pending-photo-row p{margin:0;color:var(--color-muted)}.own-pending-photo-row .spot-gallery-delete{position:static;width:2.4rem;height:2.4rem;box-shadow:none}#spot-location-map{height:360px;border:1px solid var(--color-border);border-radius:var(--radius)}.spot-lightbox{width:100vw;height:100vh;max-width:none;max-height:none;padding:3rem;border:0;background:rgba(3,7,18,.96);color:#fff}.spot-lightbox::backdrop{background:rgba(3,7,18,.96)}.spot-lightbox img{display:block;width:100%;height:100%;object-fit:contain}.spot-lightbox-close{position:absolute;top:1rem;right:1rem;color:#fff;background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.4)}@media(max-width:600px){.spot-gallery{grid-template-columns:2.75rem minmax(0,1fr) 2.75rem;gap:.5rem}.spot-lightbox{padding:3.5rem .5rem .5rem}}</style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <article>
        <header class="spot-hero-banner has-image">
            <?php if ($spot->getCategories() !== []): ?><ul class="tag-list"><?php foreach ($spot->getCategories() as $category): ?><li class="badge"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul><?php endif; ?>
            <h1><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="spot-hero-meta"><span class="spot-author"><img class="spot-author-avatar" src="<?= htmlspecialchars($spot->getAuthorAvatarPath() ?? '/images/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>" alt="" width="32" height="32"><span>Dodano przez <?= htmlspecialchars($spot->getAuthorName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></span></span><?php if ($spot->getCreatedAt() !== null): ?><time class="spot-created-at" datetime="<?= htmlspecialchars($spot->getCreatedAt()->format(DATE_ATOM), ENT_QUOTES, 'UTF-8') ?>">· <?= htmlspecialchars($spot->getCreatedAt()->format('d.m.Y'), ENT_QUOTES, 'UTF-8') ?></time><?php endif; ?></p>
            <?php if ($currentUserId !== null): ?>
                <div class="spot-hero-actions">
                    <?php if ($currentUserId !== null): ?>
                        <form class="spot-bookmark-form" action="/spot/bookmark/<?= (int) $spot->getId() ?>" method="post">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="return_to" value="/spot/details/<?= (int) $spot->getId() ?>">
                            <button class="spot-bookmark-button<?= $isBookmarked ? ' is-bookmarked' : '' ?>" type="submit" aria-label="<?= $isBookmarked ? 'Remove bookmark' : 'Save spot' ?>: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>" aria-pressed="<?= $isBookmarked ? 'true' : 'false' ?>" title="<?= $isBookmarked ? 'Saved spot' : 'Save spot' ?>">
                                <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.75h12v16.5l-6-4-6 4z"/></svg>
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if ($currentUserId !== null && $spot->getUserId() === $currentUserId): ?>
                        <form action="/spot/delete/<?= (int) $spot->getId() ?>" method="post" onsubmit="return confirm('Czy na pewno chcesz usunąć ten spot? Tej operacji nie można cofnąć.');">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <button class="spot-delete-icon" type="submit" aria-label="Usuń mój spot" title="Usuń mój spot"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-.9 14H5.9L5 6m4 4v6m6-6v6"/></svg></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </header>
        <div class="details-layout"><div>
            <?php if ($spot->getStatus() === 'pending'): ?><p class="spot-status-badge status-pending">Oczekuje na weryfikację</p><?php elseif ($spot->getStatus() === 'rejected'): ?><p class="spot-status-badge status-rejected">Odrzucony</p><?php if ($spot->getRejectionReason() !== null): ?><p class="spot-rejection-reason"><strong>Powód:</strong> <?= htmlspecialchars($spot->getRejectionReason(), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><?php endif; ?>
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
                <?php $displayImages = $images; ?>
                <section class="content-card" aria-labelledby="gallery-heading">
                    <h2 id="gallery-heading">Zdjęcia miejsca</h2>
                    <div class="spot-gallery-frame" style="position:relative">
                        <div class="spot-gallery">
                            <button type="button" class="btn btn-outline gallery-control" id="gallery-previous" aria-label="Poprzednie zdjęcie">←</button>
                            <img id="gallery-image" src="<?= htmlspecialchars($displayImages[0]['path'], ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie miejsca: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                            <button type="button" class="btn btn-outline gallery-control" id="gallery-next" aria-label="Następne zdjęcie">→</button>
                        </div>
                        <form class="spot-gallery-delete-form" id="gallery-delete-form" action="<?= !empty($displayImages[0]['is_cover']) ? '/spot/cover/delete/' . (int) $spot->getId() : '/spot/image/delete/' . (int) ($displayImages[0]['image_id'] ?? 0) ?>" method="post" onsubmit="return confirm('Usunąć to zdjęcie?');" <?= $currentUserId === null || $displayImages[0]['contributor_user_id'] !== $currentUserId || (!$displayImages[0]['is_cover'] && $displayImages[0]['image_id'] === null) ? 'hidden' : '' ?>>
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <button class="spot-gallery-delete" type="submit" aria-label="Usuń swoje zdjęcie" title="Usuń swoje zdjęcie"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-.9 14H5.9L5 6m4 4v6m6-6v6"/></svg></button>
                        </form>
                    </div>
                    <div class="form-row-between"><p class="gallery-contributor" id="gallery-contributor">Dodano przez <?= htmlspecialchars($displayImages[0]['author_name'] ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></p><span class="badge"><span id="gallery-current">1</span> / <?= count($displayImages) ?></span></div>
                    <p class="text-muted gallery-image-status" id="gallery-image-status"><?= ($displayImages[0]['status'] ?? 'approved') === 'pending' ? 'Oczekuje na weryfikację' : ((($displayImages[0]['status'] ?? 'approved') === 'rejected') ? 'Zdjęcie odrzucone' : '') ?></p>
                    <?php if ($currentUserId !== null): ?><p class="text-muted">Twoje zdjęcia przy tym spocie: <?= (int) $ownImageCount ?> / 5</p><?php endif; ?>
                </section>
                <dialog class="spot-lightbox" id="spot-lightbox" aria-label="Powiększone zdjęcie"><button class="btn btn-outline spot-lightbox-close" type="button" id="spot-lightbox-close" aria-label="Zamknij zdjęcie">Zamknij ✕</button><img id="spot-lightbox-image" alt="Powiększone zdjęcie spotu"></dialog>
            <?php endif; ?>
            <?php if ($currentUserId !== null && $ownUnpublishedImages !== []): ?>
                <section class="content-card" aria-labelledby="own-pending-photos-heading">
                    <h2 id="own-pending-photos-heading">Twoje zdjęcia oczekujące na moderację</h2>
                    <p class="text-muted">Pojawią się w galerii dopiero po zatwierdzeniu.</p>
                    <div class="own-pending-photo-list">
                        <?php foreach ($ownUnpublishedImages as $image): ?>
                            <div class="own-pending-photo-row">
                                <p><?= $image['status'] === 'pending' ? 'Oczekuje na weryfikację' : 'Zdjęcie odrzucone' ?></p>
                                <form action="/spot/image/delete/<?= (int) $image['id'] ?>" method="post" onsubmit="return confirm('Usunąć to zdjęcie?');">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="spot-gallery-delete" type="submit" aria-label="Usuń swoje zdjęcie" title="Usuń swoje zdjęcie"><svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-.9 14H5.9L5 6m4 4v6m6-6v6"/></svg></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            <?php if ($currentUserId !== null && $spot->getStatus() === 'approved'): ?>
                <section class="content-card contribute-card" aria-labelledby="contribute-heading">
                    <h2 id="contribute-heading">Contribute photos</h2>
                    <p class="text-muted">Możesz mieć maksymalnie 5 własnych zdjęć przy tym spocie (pozostało: <?= max(0, 5 - $ownImageCount) ?>). Zdjęcia zostaną opublikowane po akceptacji moderatora; zdjęcia administratora pojawią się od razu.</p>
                    <?php if ($contributionSuccess !== null): ?><p class="form-success"><?= htmlspecialchars($contributionSuccess, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    <?php if ($contributionError !== null): ?><p class="form-error"><?= htmlspecialchars($contributionError, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    <form action="/spot/contribute/<?= (int) $spot->getId() ?>" method="post" enctype="multipart/form-data" class="contribute-form">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <label for="contribution-images">Zdjęcia (JPEG lub PNG)</label>
                        <input type="file" id="contribution-images" name="images[]" accept="image/jpeg,image/png" multiple required <?= $ownImageCount >= 5 ? 'disabled' : '' ?>>
                        <button class="btn btn-primary" type="submit">Contribute</button>
                    </form>
                </section>
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
const galleryImages = <?= json_encode($displayImages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;
let galleryIndex = 0;
const galleryImage = document.getElementById('gallery-image');
const lightbox = document.getElementById('spot-lightbox');
function showGalleryImage(index) {
    galleryIndex = (index + galleryImages.length) % galleryImages.length;
    const image = galleryImages[galleryIndex];
    galleryImage.src = image.path;
    document.getElementById('gallery-current').textContent = String(galleryIndex + 1);
    document.getElementById('gallery-contributor').textContent = `Dodano przez ${image.author_name || 'Użytkownik'}`;
    document.getElementById('gallery-image-status').textContent = image.status === 'pending' ? 'Oczekuje na weryfikację' : (image.status === 'rejected' ? 'Zdjęcie odrzucone' : '');
    const deleteForm = document.getElementById('gallery-delete-form');
    const canDelete = image.contributor_user_id === <?= json_encode($currentUserId, JSON_THROW_ON_ERROR) ?> && (image.is_cover || image.image_id !== null);
    deleteForm.hidden = !canDelete;
    if (canDelete) deleteForm.action = image.is_cover ? `/spot/cover/delete/<?= (int) $spot->getId() ?>` : `/spot/image/delete/${image.image_id}`;
}
galleryImage.addEventListener('click', () => { document.getElementById('spot-lightbox-image').src = galleryImages[galleryIndex].path; lightbox.showModal(); });
document.getElementById('spot-lightbox-close').addEventListener('click', () => lightbox.close());
lightbox.addEventListener('click', event => { if (event.target === lightbox) lightbox.close(); });
if (galleryImages.length > 1) {
    document.getElementById('gallery-previous').addEventListener('click', () => showGalleryImage(galleryIndex - 1));
    document.getElementById('gallery-next').addEventListener('click', () => showGalleryImage(galleryIndex + 1));
}
<?php endif; ?>
</script>
</body>
</html>
