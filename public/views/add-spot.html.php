<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="/css/style.css">
    <style>#spot-map{height:340px;border:1px solid var(--color-border);border-radius:var(--radius)}.upload-dropzone input{width:100%;color:var(--color-text)}.form-error{color:#b42318}</style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page form-container">
    <header class="auth-header"><span class="radar-status">● NOWE MIEJSCE</span><h1 id="add-spot-heading">Dodaj spot</h1><p class="text-muted">Podziel się ciekawą lokalizacją ze społecznością SpotScout.</p></header>
    <?php if ($errors !== []): ?><div class="content-card form-error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form action="/spot/add" method="post" enctype="multipart/form-data" class="form-stack">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <section class="content-card form-stack" aria-labelledby="spot-info-heading">
            <h2 id="spot-info-heading">Informacje o miejscu</h2>
            <div class="form-group"><label for="title" class="form-label-row">Nazwa spotu</label><input class="form-input" type="text" id="title" name="title" maxlength="150" placeholder="np. Stara hala elektrowni" required></div>
            <div class="form-group"><label for="category" class="form-label-row">Kategoria</label><select class="form-select" id="category" name="category" required><option value="">Wybierz kategorię</option><?php foreach ($categories as $category): ?><option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label for="image" class="form-label-row">Zdjęcia miejsca <span class="form-hint">JPEG lub PNG, maksymalnie 5 zdjęć po 5 MB</span></label><div class="upload-dropzone"><input type="file" id="image" name="image[]" accept="image/jpeg,image/png" multiple required></div></div>
        </section>
        <section class="content-card form-stack" aria-labelledby="coordinates-heading">
            <div class="form-row-between"><h2 id="coordinates-heading">Lokalizacja na mapie</h2><span class="badge">Wymagane</span></div>
            <p class="text-muted">Kliknij mapę, aby ustawić pinezkę.</p><div id="spot-map" aria-label="Mapa wyboru lokalizacji"></div>
            <input type="hidden" id="latitude" name="latitude" required><input type="hidden" id="longitude" name="longitude" required>
        </section>
        <div class="hero-actions" style="justify-content:flex-end"><a class="btn btn-outline" href="/explore">Anuluj</a><button class="btn btn-primary" type="submit">Dodaj spot →</button></div>
    </form>
</main>
<nav class="mobile-bottom-nav" aria-label="Nawigacja mobilna"><a href="/explore" class="mobile-nav-item">Explore</a><a href="/spot/add" class="mobile-nav-item mobile-nav-add is-active">+ Dodaj</a><a href="/profile" class="mobile-nav-item">Profil</a></nav>
<footer class="page site-footer"><p>&copy; 2026 SpotScout.</p></footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/js/theme.js" defer></script>
<script>
const map = L.map('spot-map').setView([52.1, 19.4], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
let marker;
map.on('click', event => { document.getElementById('latitude').value = event.latlng.lat.toFixed(8); document.getElementById('longitude').value = event.latlng.lng.toFixed(8); if (marker) marker.setLatLng(event.latlng); else marker = L.marker(event.latlng).addTo(map); });
</script>
</body>
</html>
