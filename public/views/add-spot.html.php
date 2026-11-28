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
        #spot-map{height:340px;border:1px solid var(--color-border);border-radius:var(--radius)}
        .upload-dropzone input{width:100%;color:var(--color-text)}
        .form-error{color:#b42318}
        .category-fieldset{display:grid;gap:.65rem!important}
        .category-fieldset legend{display:grid;justify-content:start;gap:.3rem}
        .category-options{display:flex;flex-wrap:wrap;gap:.65rem}
        .category-option{position:relative;display:inline-flex;align-items:center;justify-content:center;min-height:2.7rem;padding:.65rem 1rem;border:1px solid var(--color-border);border-radius:var(--radius-pill);background:var(--color-background);color:var(--color-muted);font-weight:700;cursor:pointer;transition:background-color .15s ease,color .15s ease,border-color .15s ease,transform .15s ease}
        .category-option:hover{border-color:var(--color-brand);transform:translateY(-1px)}
        .category-option input{position:absolute;width:1px;height:1px;opacity:0}
        .category-option:has(input:checked){background:var(--color-brand);border-color:var(--color-brand);color:#fff}
        .category-option:focus-within{outline:3px solid color-mix(in srgb,var(--color-brand) 35%,transparent);outline-offset:2px}
        .upload-dropzone{display:grid;gap:.5rem;text-align:left}
        .upload-dropzone input[type=file]::file-selector-button,.contribute-form input[type=file]::file-selector-button{margin-right:.75rem;padding:.65rem .95rem;border:1px solid var(--color-brand);border-radius:var(--radius-pill);background:var(--color-brand);color:#fff;font:inherit;font-weight:700;cursor:pointer;transition:filter .15s ease}
        .upload-dropzone input[type=file]::file-selector-button:hover,.contribute-form input[type=file]::file-selector-button:hover{filter:brightness(1.08)}
    </style>
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
            <fieldset class="form-group category-fieldset" style="border:0;padding:0;margin:0"><legend class="form-label-row">Kategorie <span class="form-hint">Możesz wybrać kilka</span></legend><div class="category-options"><?php foreach ($categories as $category): ?><label class="category-option"><input type="checkbox" name="categories[]" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
            <div class="form-group"><label for="image" class="form-label-row">Zdjęcia miejsca <span class="form-hint">JPEG lub PNG, od 1 do 5 zdjęć po maksymalnie 5 MB</span></label><div class="upload-dropzone"><input type="file" id="image" name="image[]" accept="image/jpeg,image/png" multiple required></div></div>
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
