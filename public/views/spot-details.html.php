<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="/css/style.css?v=spot-gallery-review-1">
    <style>.spot-hero-banner.has-image{background-image:linear-gradient(90deg,rgba(15,23,42,.9),rgba(15,23,42,.35)),url('<?= htmlspecialchars($spot->getPreviewImagePath() ?? '/images/featured-spot.svg', ENT_QUOTES, 'UTF-8') ?>');background-size:cover;background-position:center}.spot-hero-banner h1{margin-top:var(--space-1)}.spot-hero-meta{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem 1rem}.spot-author{color:#cbd5e1;display:inline-flex;align-items:center;gap:.5rem;line-height:1.4}.spot-created-at{white-space:nowrap;line-height:1.4}.spot-author-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;flex:0 0 32px}.spot-gallery{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:var(--space-2)}.spot-gallery img{width:100%;max-height:32rem;object-fit:contain;border-radius:var(--radius-sm);background:var(--color-background);cursor:zoom-in}.gallery-control{min-width:2.75rem}.spot-gallery-delete-form{position:absolute;top:.75rem;right:4.75rem;margin:0;z-index:1}.spot-gallery-delete{width:2.75rem;height:2.75rem;display:grid;place-items:center;border:1px solid #fda29b;border-radius:50%;background:#fff1f0;color:#b42318;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.22)}.spot-gallery-delete:hover{background:#fee4e2}.gallery-contributor{margin:.6rem 0 0;color:var(--color-muted);font-size:.9rem}.gallery-image-status:empty{display:none}.own-pending-photo-list{display:grid;gap:.65rem}.own-pending-photo-row{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.65rem .8rem;border:1px solid var(--color-border);border-radius:var(--radius-sm)}.own-pending-photo-row p{margin:0;color:var(--color-muted)}.own-pending-photo-row .spot-gallery-delete{position:static;width:2.4rem;height:2.4rem;box-shadow:none}#spot-location-map{height:360px;border:1px solid var(--color-border);border-radius:var(--radius)}.spot-lightbox{width:100vw;height:100vh;max-width:none;max-height:none;padding:3rem;border:0;background:rgba(3,7,18,.96);color:#fff}.spot-lightbox::backdrop{background:rgba(3,7,18,.96)}.spot-lightbox img{display:block;width:100%;height:100%;object-fit:contain}.spot-lightbox-close{position:absolute;top:1rem;right:1rem;color:#fff;background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.4)}.spot-report-dialog{width:min(34rem,calc(100vw - 2rem));border:1px solid var(--color-border);border-radius:var(--radius);padding:0;background:var(--color-surface);color:var(--color-text);box-shadow:0 1rem 3rem rgba(0,0,0,.35)}.spot-report-dialog::backdrop{background:rgba(3,7,18,.72)}.spot-report-form{display:grid;gap:.75rem;padding:1.5rem}.spot-report-form h2{margin:0 2rem .5rem 0}.spot-report-form label{font-weight:700}.spot-report-form textarea{resize:vertical}.spot-report-close{position:absolute;top:.75rem;right:.75rem;width:2.25rem;height:2.25rem;border:0;border-radius:50%;background:transparent;color:inherit;font-size:1.5rem;cursor:pointer}.spot-report-trigger{margin:.75rem 0}#report-location-map{height:18rem;border:1px solid var(--color-border);border-radius:var(--radius-sm)}#report-location-picker[hidden]{display:none}.report-submitted{padding:.8rem 1rem;border-radius:var(--radius-sm);background:var(--color-surface);color:var(--color-brand)}@media(max-width:600px){.spot-gallery{grid-template-columns:2.75rem minmax(0,1fr) 2.75rem;gap:.5rem}.spot-lightbox{padding:3.5rem .5rem .5rem}}</style>
    <style>
        .report-photo-choice-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(11rem,1fr));gap:.6rem}
        .report-photo-choice{display:flex;align-items:center;gap:.55rem;padding:.55rem;border:1px solid var(--color-border);border-radius:var(--radius-sm);cursor:pointer}
        .report-photo-choice input{width:1rem;height:1rem;flex:0 0 auto}
        .report-photo-choice img{width:4.5rem;height:3.5rem;object-fit:cover;border-radius:.35rem}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <?php if ($reportSubmitted): ?><p class="report-submitted" role="status">Dziękujemy. Zgłoszenie zostało przekazane do moderacji.</p><?php endif; ?>
    <?php if ($moderationFlash !== null): ?><p class="report-submitted" role="status"><?= htmlspecialchars($moderationFlash, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
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
                    <?php if ($currentUserId !== null && ($spot->getUserId() === $currentUserId || in_array($currentUserRole, ['admin', 'moderator'], true))): ?>
                        <a class="btn btn-outline" href="/spot/edit?id=<?= (int) $spot->getId() ?>">Edytuj Spot</a>
                        <button class="spot-delete-icon" type="button" id="open-delete-spot" aria-label="Usuń spot" title="Usuń spot"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-.9 14H5.9L5 6m4 4v6m6-6v6"/></svg></button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($currentUserRole === 'user' && $spot->getStatus() === 'approved'): ?>
                <button class="btn btn-outline spot-report-trigger" type="button" id="open-report-dialog">Zgłoś Spot</button>
            <?php endif; ?>
        </header>
        <?php if ($currentUserId !== null && ($spot->getUserId() === $currentUserId || in_array($currentUserRole, ['admin', 'moderator'], true))): ?>
            <dialog class="spot-report-dialog" id="delete-spot-dialog" aria-labelledby="delete-spot-title">
                <form action="/spot/delete/<?= (int) $spot->getId() ?>" method="post" class="spot-report-form">
                    <button class="spot-report-close" type="button" id="close-delete-spot" aria-label="Zamknij">×</button>
                    <?php $moderatorEscalation = $currentUserRole === 'moderator' && $spot->getUserId() !== $currentUserId; ?>
                    <?php $adminDeletion = $currentUserRole === 'admin' && $spot->getUserId() !== $currentUserId; ?>
                    <h2 id="delete-spot-title"><?= $moderatorEscalation ? 'Oznacz spot do usunięcia' : 'Usuń spot' ?></h2>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <?php if ($moderatorEscalation || $adminDeletion): ?>
                        <label for="delete-spot-reason">Powód</label>
                        <textarea id="delete-spot-reason" name="reason" rows="4" maxlength="1000" required placeholder="Podaj powód usunięcia lub eskalacji"></textarea>
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit"><?= $moderatorEscalation ? 'Eskaluj do Admina' : 'Potwierdź usunięcie' ?></button>
                </form>
            </dialog>
        <?php endif; ?>
        <?php if ($currentUserRole === 'user' && $spot->getStatus() === 'approved'): ?>
            <dialog class="spot-report-dialog" id="report-dialog" aria-labelledby="report-dialog-title">
                <form action="/spot/report" method="post" class="spot-report-form">
                    <button class="spot-report-close" type="button" id="close-report-dialog" aria-label="Zamknij">×</button>
                    <h2 id="report-dialog-title">Zgłoś Spot</h2>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="spot_id" value="<?= (int) $spot->getId() ?>">
                    <label for="report-type">Powód zgłoszenia</label>
                    <select id="report-type" name="report_type" required><option value="">Wybierz powód</option><option value="category">Kategoria</option><option value="location">Lokalizacja</option><option value="photo">Zdjęcie</option><option value="delete">Usunięcie spotu</option></select>
                    <div id="report-location-picker" hidden>
                        <p class="text-muted">Kliknij mapę, aby wskazać proponowaną lokalizację.</p>
                        <div id="report-location-map" aria-label="Wybierz proponowaną lokalizację"></div>
                        <input type="hidden" id="report-proposed-latitude" name="proposed_latitude">
                        <input type="hidden" id="report-proposed-longitude" name="proposed_longitude">
                    </div>
                    <div id="report-photo-picker" hidden>
                        <p class="text-muted">Zaznacz zdjęcia, które wymagają usunięcia.</p>
                        <?php if ($images === []): ?><p class="text-muted">Ten spot nie ma zdjęć do zgłoszenia.</p><?php else: ?><div class="report-photo-choice-list"><?php foreach ($images as $image): ?>
                            <label class="report-photo-choice"><input type="checkbox" name="photo_keys[]" value="<?= $image['is_cover'] ? 'cover' : (int) $image['image_id'] ?>"><img src="<?= htmlspecialchars($image['path'], ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie spotu" loading="lazy"><span><?= $image['is_cover'] ? 'Zdjęcie główne' : 'Zdjęcie #' . (int) $image['image_id'] ?></span></label>
                        <?php endforeach; ?></div><?php endif; ?>
                    </div>
                    <label for="report-description">Opis</label>
                    <textarea id="report-description" name="description" rows="5" maxlength="2000" required placeholder="Opisz, co wymaga poprawy"></textarea>
                    <button class="btn btn-primary" type="submit">Wyślij zgłoszenie</button>
                </form>
            </dialog>
        <?php endif; ?>
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
                    </div>
                    <div class="form-row-between"><p class="gallery-contributor" id="gallery-contributor">Dodano przez <?= htmlspecialchars($displayImages[0]['author_name'] ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></p><span class="badge"><span id="gallery-current">1</span> / <?= count($displayImages) ?></span></div>
                    <p class="text-muted gallery-image-status" id="gallery-image-status"><?= ($displayImages[0]['status'] ?? 'approved') === 'pending' ? 'Oczekuje na weryfikację' : ((($displayImages[0]['status'] ?? 'approved') === 'rejected') ? 'Zdjęcie odrzucone' : '') ?></p>
                    <?php if ($currentUserId !== null): ?><p class="text-muted">Twoje zdjęcia przy tym spocie: <?= (int) $ownImageCount ?> / 5</p><?php endif; ?>
                </section>
                <dialog class="spot-lightbox" id="spot-lightbox" aria-label="Powiększone zdjęcie"><button class="btn btn-outline spot-lightbox-close" type="button" id="spot-lightbox-close" aria-label="Zamknij zdjęcie">Zamknij ✕</button><img id="spot-lightbox-image" alt="Powiększone zdjęcie spotu"></dialog>
            <?php endif; ?>

            <?php if ($currentUserId !== null && $spot->getStatus() === 'approved'): ?>
                <section class="content-card contribute-card" aria-labelledby="contribute-heading">
                    <h2 id="contribute-heading">Contribute photos</h2>
                    <p class="text-muted">Możesz mieć maksymalnie 5 własnych zdjęć przy tym spocie (pozostało: <?= max(0, 5 - $ownImageCount) ?>). Zdjęcia zostaną opublikowane po akceptacji moderatora; zdjęcia administratora pojawią się od razu.</p>
                    <?php if ($contributionSuccess !== null): ?><p class="form-success"><?= htmlspecialchars($contributionSuccess, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    <?php if ($contributionError !== null): ?><p class="form-error"><?= htmlspecialchars($contributionError, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                    <?php if ($ownUnpublishedImages !== []): ?>
                        <div class="own-pending-photo-list" aria-label="Status Twoich zdjęć">
                            <?php foreach ($ownUnpublishedImages as $image): ?>
                                <div class="own-pending-photo-row">
                                    <p><?= $image['status'] === 'pending' ? 'Twoje zdjęcie oczekuje na akceptację moderatora.' : 'Twoje zdjęcie zostało odrzucone.' ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($ownImageCount > 0): ?><p><a class="btn btn-outline" href="/spot/edit?id=<?= (int) $spot->getId() ?>">Zarządzaj moimi zdjęciami</a></p><?php endif; ?>
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
const reportDialog = document.getElementById('report-dialog');
const deleteSpotDialog = document.getElementById('delete-spot-dialog');
document.getElementById('open-delete-spot')?.addEventListener('click', () => deleteSpotDialog.showModal());
document.getElementById('close-delete-spot')?.addEventListener('click', () => deleteSpotDialog.close());
let reportLocationMap = null;
let reportLocationMarker = null;
const reportTypeSelect = document.getElementById('report-type');
const reportLocationPicker = document.getElementById('report-location-picker');
const reportPhotoPicker = document.getElementById('report-photo-picker');
function initReportLocationMap() {
    if (reportLocationMap) { reportLocationMap.invalidateSize(); return; }
    const originalLat = <?= json_encode($spot->getLatitude(), JSON_THROW_ON_ERROR) ?>;
    const originalLng = <?= json_encode($spot->getLongitude(), JSON_THROW_ON_ERROR) ?>;
    const initialCenter = originalLat !== null && originalLng !== null ? [originalLat, originalLng] : [52.2297, 21.0122];
    reportLocationMap = L.map('report-location-map').setView(initialCenter, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(reportLocationMap);
    if (originalLat !== null && originalLng !== null) L.marker(initialCenter).addTo(reportLocationMap).bindPopup('Obecna lokalizacja');
    reportLocationMap.on('click', event => {
        const {lat, lng} = event.latlng;
        document.getElementById('report-proposed-latitude').value = lat.toFixed(8);
        document.getElementById('report-proposed-longitude').value = lng.toFixed(8);
        if (reportLocationMarker) reportLocationMarker.setLatLng(event.latlng);
        else reportLocationMarker = L.marker(event.latlng).addTo(reportLocationMap);
    });
}
document.getElementById('open-report-dialog')?.addEventListener('click', () => reportDialog.showModal());
document.getElementById('close-report-dialog')?.addEventListener('click', () => reportDialog.close());
reportTypeSelect?.addEventListener('change', () => {
    const isLocationReport = reportTypeSelect.value === 'location';
    const isPhotoReport = reportTypeSelect.value === 'photo';
    reportLocationPicker.hidden = !isLocationReport;
    reportPhotoPicker.hidden = !isPhotoReport;
    document.getElementById('report-proposed-latitude').required = isLocationReport;
    document.getElementById('report-proposed-longitude').required = isLocationReport;
    if (isLocationReport) requestAnimationFrame(initReportLocationMap);
});
document.querySelector('.spot-report-form')?.addEventListener('submit', event => {
    if (reportTypeSelect.value === 'location'
        && (!document.getElementById('report-proposed-latitude').value || !document.getElementById('report-proposed-longitude').value)) {
        event.preventDefault();
        alert('Zaznacz proponowaną lokalizację na mapie.');
        return;
    }
    if (reportTypeSelect.value === 'photo' && reportPhotoPicker.querySelectorAll('input[name="photo_keys[]"]:checked').length === 0) {
        event.preventDefault();
        alert('Zaznacz przynajmniej jedno zdjęcie.');
    }
});
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
    document.getElementById('gallery-image-status').textContent = image.status === 'pending' ? 'Oczekuje na weryfikację' : (image.status === 'rejected' ? 'Zdjęcie odrzucone' : '');}
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
