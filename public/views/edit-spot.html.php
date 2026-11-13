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
    <?php if ($canEditMetadata): ?><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><?php endif; ?>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .edit-spot-form{display:grid;gap:1.2rem;max-width:58rem;margin:2rem auto}
        .edit-spot-form fieldset{display:grid;grid-template-columns:repeat(auto-fit,minmax(12rem,1fr));gap:.7rem;border:1px solid var(--color-border);border-radius:var(--radius);padding:1rem}
        .edit-spot-form fieldset legend{padding:0 .4rem;font-weight:700}
        .edit-spot-category{display:flex;align-items:center;gap:.55rem}
        .edit-spot-category input{width:1.1rem;height:1.1rem}
        #edit-spot-map{height:24rem;border:1px solid var(--color-border);border-radius:var(--radius)}
        .edit-spot-errors{padding:1rem;border:1px solid #fda29b;border-radius:var(--radius);color:#b42318}
        .edit-photos{display:grid;grid-template-columns:repeat(auto-fill,minmax(14rem,1fr));gap:1rem;margin-top:1rem}
        .edit-photo-card{display:grid;align-content:start;gap:.75rem;padding:1rem;border:1px solid var(--color-border);border-radius:var(--radius)}
        .edit-photo-card img{width:100%;height:10rem;object-fit:cover;border-radius:var(--radius-sm)}
        .edit-photo-card p{margin:0;color:var(--color-muted)}
        .edit-photo-delete-form{display:grid;gap:.6rem}
        .edit-photo-delete-form textarea{resize:vertical;min-height:5rem}
        .edit-photo-delete-form .spot-gallery-delete{position:static;width:auto;height:auto;border-radius:var(--radius-sm);box-shadow:none;padding:.6rem .9rem;font:inherit}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <section class="content-card">
        <p class="eyebrow">SpotScout</p>
        <h1><?= $canEditMetadata ? 'Edytuj Spot' : 'Zarządzaj swoimi zdjęciami' ?></h1>
        <p class="text-muted"><?= $canEditMetadata ? 'Zmień nazwę, kategorie lub wskaż nowe miejsce na mapie.' : 'Możesz usunąć zdjęcia dodane przez siebie do tego spotu.' ?></p>
        <?php if ($errors !== []): ?><div class="edit-spot-errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>

        <?php if ($canEditMetadata): ?>
            <form class="edit-spot-form" action="/spot/edit" method="post">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="spot_id" value="<?= (int) $spot->getId() ?>">
                <label for="spot-title">Nazwa spotu</label>
                <input id="spot-title" name="title" type="text" maxlength="150" required value="<?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                <fieldset>
                    <legend>Kategorie</legend>
                    <?php foreach ($categories as $category): ?>
                        <label class="edit-spot-category"><input type="checkbox" name="categories[]" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($category, $spot->getCategories(), true) ? 'checked' : '' ?>><span><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span></label>
                    <?php endforeach; ?>
                </fieldset>
                <p class="text-muted">Kliknij mapę, aby ustawić pinezkę w nowej lokalizacji.</p>
                <div id="edit-spot-map" aria-label="Wybierz lokalizację spotu na mapie"></div>
                <div class="form-row">
                    <label for="spot-latitude">Szerokość geograficzna</label>
                    <input id="spot-latitude" name="latitude" type="number" step="any" min="-90" max="90" required value="<?= htmlspecialchars((string) ($spot->getLatitude() ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <label for="spot-longitude">Długość geograficzna</label>
                    <input id="spot-longitude" name="longitude" type="number" step="any" min="-180" max="180" required value="<?= htmlspecialchars((string) ($spot->getLongitude() ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <?php if ($moderationReasonRequired): ?>
                    <label for="moderation-reason">Powód edycji (widoczny w powiadomieniu właściciela)</label>
                    <textarea id="moderation-reason" name="moderation_reason" rows="4" maxlength="1000" required><?= htmlspecialchars($moderationReason, ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php endif; ?>
                <div class="form-actions"><button class="btn btn-primary" type="submit">Zapisz zmiany</button><a class="btn btn-outline" href="/spot/details/<?= (int) $spot->getId() ?>">Anuluj</a></div>
            </form>
        <?php endif; ?>
    </section>

    <section class="content-card" aria-labelledby="edit-photos-heading">
        <h2 id="edit-photos-heading">Zdjęcia spotu</h2>
        <p class="text-muted">Zdjęcia można usunąć z tego widoku. Autor zdjęcia otrzyma powiadomienie.</p>
        <div class="edit-photos">
            <?php $visiblePhotoCount = 0; ?>
            <?php foreach ($images as $image): ?>
                <?php if ($canEditMetadata || $image['contributor_user_id'] === $currentUserId): $visiblePhotoCount++; ?>
                    <article class="edit-photo-card">
                        <img src="<?= htmlspecialchars($image['path'], ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie spotu: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                        <p><?= $image['is_cover'] ? 'Zdjęcie główne' : 'Dodano przez ' . htmlspecialchars($image['author_name'] ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></p>
                        <form class="edit-photo-delete-form" action="/spot/photo/delete" method="post" onsubmit="return confirm('Usunąć to zdjęcie?');">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="spot_id" value="<?= (int) $spot->getId() ?>">
                            <input type="hidden" name="photo_key" value="<?= $image['is_cover'] ? 'cover' : (int) $image['image_id'] ?>">
                            <?php if (in_array($currentUserRole, ['admin', 'moderator'], true) && $spot->getUserId() !== $currentUserId): ?>
                                <label for="photo-reason-<?= $image['is_cover'] ? 'cover' : (int) $image['image_id'] ?>">Powód usunięcia</label>
                                <textarea id="photo-reason-<?= $image['is_cover'] ? 'cover' : (int) $image['image_id'] ?>" name="reason" maxlength="1000" required placeholder="Podaj powód usunięcia zdjęcia"></textarea>
                            <?php endif; ?>
                            <button class="btn btn-outline" type="submit">Usuń zdjęcie</button>
                        </form>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($visiblePhotoCount === 0): ?><p class="text-muted">Brak zdjęć, którymi możesz zarządzać.</p><?php endif; ?>
        </div>

        <?php if ($ownUnpublishedImages !== []): ?>
            <h3>Twoje zdjęcia oczekujące na moderację</h3>
            <div class="own-pending-photo-list">
                <?php foreach ($ownUnpublishedImages as $image): ?>
                    <div class="own-pending-photo-row">
                        <p><?= $image['status'] === 'pending' ? 'Oczekuje na weryfikację' : 'Zdjęcie odrzucone' ?></p>
                        <form class="edit-photo-delete-form" action="/spot/image/delete/<?= (int) $image['id'] ?>" method="post" onsubmit="return confirm('Usunąć to zdjęcie?');">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                            <button class="spot-gallery-delete" type="submit" aria-label="Usuń swoje zdjęcie" title="Usuń swoje zdjęcie"><svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-.9 14H5.9L5 6m4 4v6m6-6v6"/></svg></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <p><a class="btn btn-outline" href="/spot/details/<?= (int) $spot->getId() ?>">Wróć do strony spotu</a></p>
    </section>
</main>
<?php if ($canEditMetadata): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const latitudeInput = document.getElementById('spot-latitude');
    const longitudeInput = document.getElementById('spot-longitude');
    const initialLatitude = Number(latitudeInput.value) || 52.2297;
    const initialLongitude = Number(longitudeInput.value) || 21.0122;
    const map = L.map('edit-spot-map').setView([initialLatitude, initialLongitude], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
    let marker = L.marker([initialLatitude, initialLongitude]).addTo(map);
    map.on('click', event => {
        latitudeInput.value = event.latlng.lat.toFixed(8);
        longitudeInput.value = event.latlng.lng.toFixed(8);
        marker.setLatLng(event.latlng);
    });
    </script>
<?php endif; ?>
<script src="/js/theme.js" defer></script>
</body>
</html>
