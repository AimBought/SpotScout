<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="/css/style.css">
    <style>.spot-hero-banner.has-image{background-image:linear-gradient(90deg,rgba(15,23,42,.9),rgba(15,23,42,.35)),url('<?= htmlspecialchars($spot->getImagePath() ?? '', ENT_QUOTES, 'UTF-8') ?>');background-size:cover;background-position:center}.spot-hero-banner h1{margin-top:var(--space-1)}.spot-author{color:#cbd5e1}.spot-gallery{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:var(--space-2)}.spot-gallery img{width:100%;max-height:32rem;object-fit:contain;border-radius:var(--radius-sm);background:var(--color-background)}.gallery-control{min-width:2.75rem}</style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <article>
        <header class="spot-hero-banner<?= $spot->getImagePath() !== null ? ' has-image' : '' ?>">
            <?php if ($spot->getCategory() !== null): ?><ul class="tag-list"><li class="badge"><?= htmlspecialchars($spot->getCategory(), ENT_QUOTES, 'UTF-8') ?></li></ul><?php endif; ?>
            <h1><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="spot-hero-meta"><span class="spot-author">Dodano przez <?= htmlspecialchars($spot->getAuthorName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></span><?php if ($spot->getCreatedAt() !== null): ?> · <?= htmlspecialchars($spot->getCreatedAt()->format('d.m.Y'), ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
        </header>
        <div class="details-layout"><div>
            <section class="content-card" aria-labelledby="spot-info-heading"><h2 id="spot-info-heading">Informacje o miejscu</h2>
                <p><?= htmlspecialchars($spot->getDescription() ?? 'Brak dodatkowego opisu tego miejsca.', ENT_QUOTES, 'UTF-8') ?></p>
                <dl class="info-grid">
                    <div class="info-tile"><dt>Kategoria</dt><dd><?= htmlspecialchars($spot->getCategory() ?? 'Nie podano', ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div class="info-tile"><dt>Współrzędne GPS</dt><dd><?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?><?= htmlspecialchars(number_format($spot->getLatitude(), 6, '.', '') . ', ' . number_format($spot->getLongitude(), 6, '.', ''), ENT_QUOTES, 'UTF-8') ?><?php else: ?>Nie podano<?php endif; ?></dd></div>
                    <div class="info-tile"><dt>Dodane przez</dt><dd><?= htmlspecialchars($spot->getAuthorName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
                <?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?><a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="https://www.openstreetmap.org/?mlat=<?= rawurlencode((string) $spot->getLatitude()) ?>&amp;mlon=<?= rawurlencode((string) $spot->getLongitude()) ?>#map=16/<?= rawurlencode((string) $spot->getLatitude()) ?>/<?= rawurlencode((string) $spot->getLongitude()) ?>">Otwórz w OpenStreetMap</a><?php endif; ?>
            </section>
            <?php if ($images !== []): ?>
                <section class="content-card" aria-labelledby="gallery-heading">
                    <div class="form-row-between"><h2 id="gallery-heading">Zdjęcia miejsca</h2><span class="badge"><span id="gallery-current">1</span> / <?= count($images) ?></span></div>
                    <div class="spot-gallery">
                        <button type="button" class="btn btn-outline gallery-control" id="gallery-previous" aria-label="Poprzednie zdjęcie">←</button>
                        <img id="gallery-image" src="<?= htmlspecialchars($images[0], ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie miejsca: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="button" class="btn btn-outline gallery-control" id="gallery-next" aria-label="Następne zdjęcie">→</button>
                    </div>
                </section>
            <?php endif; ?>
            <a class="btn btn-outline" href="/explore">← Wróć do Explore</a>
        </div></div>
    </article>
</main>
<nav class="mobile-bottom-nav" aria-label="Nawigacja mobilna"><a href="/explore" class="mobile-nav-item">Explore</a><a href="/spot/add" class="mobile-nav-item mobile-nav-add">+ Dodaj</a><a href="/profile" class="mobile-nav-item">Profil</a></nav>
<footer class="page site-footer"><p>&copy; 2026 SpotScout.</p></footer>
<script src="/js/theme.js" defer></script>
<?php if (count($images) > 1): ?>
<script>
const galleryImages = <?= json_encode($images, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>;
let galleryIndex = 0;
function showGalleryImage(index) { galleryIndex = (index + galleryImages.length) % galleryImages.length; document.getElementById('gallery-image').src = galleryImages[galleryIndex]; document.getElementById('gallery-current').textContent = String(galleryIndex + 1); }
document.getElementById('gallery-previous').addEventListener('click', () => showGalleryImage(galleryIndex - 1));
document.getElementById('gallery-next').addEventListener('click', () => showGalleryImage(galleryIndex + 1));
</script>
<?php endif; ?>
</body>
</html>
