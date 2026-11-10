<?php if ($pendingSpots === []): ?>
    <p class="content-card text-muted">Brak spot&oacute;w oczekuj&#261;cych na moderacj&#281;.</p>
<?php else: ?>
    <div class="pending-spot-list">
        <?php foreach ($pendingSpots as $spot): ?>
            <article class="content-card pending-spot-card">
                <div class="pending-spot-details">
                    <div>
                        <h2><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="text-muted">Dodano przez <?= htmlspecialchars($spot->getAuthorName() ?? 'Uzytkownik', ENT_QUOTES, 'UTF-8') ?></p>
                        <?php if ($spot->getCategories() !== []): ?>
                            <ul class="tag-list"><?php foreach ($spot->getCategories() as $category): ?><li class="badge"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php $spotImages = $pendingSpotImages[(int) $spot->getId()] ?? []; ?>
                <?php if ($spotImages !== []): ?>
                    <div class="pending-spot-gallery" data-images="<?= htmlspecialchars(json_encode($spotImages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8') ?>">
                        <button class="btn btn-outline pending-gallery-previous" type="button" aria-label="Poprzednie zdjęcie">&#8592;</button>
                        <img class="pending-spot-gallery-image" src="<?= htmlspecialchars($spotImages[0], ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie spotu: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>" tabindex="0">
                        <button class="btn btn-outline pending-gallery-next" type="button" aria-label="Następne zdjęcie">&#8594;</button>
                        <span class="badge pending-gallery-count">1 / <?= count($spotImages) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?>
                    <div id="pending-spot-map-<?= (int) $spot->getId() ?>" class="pending-spot-map" data-latitude="<?= htmlspecialchars((string) $spot->getLatitude(), ENT_QUOTES, 'UTF-8') ?>" data-longitude="<?= htmlspecialchars((string) $spot->getLongitude(), ENT_QUOTES, 'UTF-8') ?>" data-title="<?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>" aria-label="Mapa lokalizacji spotu"></div>
                <?php else: ?>
                    <p class="text-muted">Lokalizacja nie zosta&#322;a podana.</p>
                <?php endif; ?>
                <div class="moderation-actions">
                    <form action="/moderate/approve" method="post">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="spot_id" value="<?= (int) $spot->getId() ?>">
                        <button class="btn btn-primary" type="submit">Zatwierd&#378;</button>
                    </form>
                    <form action="/moderate/reject" method="post" class="moderation-reject-form">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="spot_id" value="<?= (int) $spot->getId() ?>">
                        <label for="reason-<?= (int) $spot->getId() ?>">Pow&oacute;d odrzucenia</label>
                        <textarea id="reason-<?= (int) $spot->getId() ?>" name="reason" maxlength="1000" required></textarea>
                        <button class="btn btn-danger" type="submit">Odrzu&#263; spot</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <dialog class="pending-image-lightbox" id="pending-image-lightbox" aria-label="Powiększone zdjęcie spotu">
        <button class="btn btn-outline pending-lightbox-close" id="pending-lightbox-close" type="button" aria-label="Zamknij">Zamknij &#10005;</button>
        <button class="btn btn-outline pending-lightbox-previous" id="pending-lightbox-previous" type="button" aria-label="Poprzednie zdjęcie">&#8592;</button>
        <img id="pending-lightbox-image" alt="Powiększone zdjęcie spotu">
        <button class="btn btn-outline pending-lightbox-next" id="pending-lightbox-next" type="button" aria-label="Następne zdjęcie">&#8594;</button>
    </dialog>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const pendingLightbox = document.getElementById('pending-image-lightbox');
        const pendingLightboxImage = document.getElementById('pending-lightbox-image');
        let activePendingImages = [];
        let activePendingIndex = 0;
        function showPendingLightboxImage(index) {
            activePendingIndex = (index + activePendingImages.length) % activePendingImages.length;
            pendingLightboxImage.src = activePendingImages[activePendingIndex];
        }
        document.querySelectorAll('.pending-spot-gallery').forEach((gallery) => {
            const images = JSON.parse(gallery.dataset.images);
            const image = gallery.querySelector('.pending-spot-gallery-image');
            const counter = gallery.querySelector('.pending-gallery-count');
            let index = 0;
            const showImage = (nextIndex) => {
                index = (nextIndex + images.length) % images.length;
                image.src = images[index];
                counter.textContent = `${index + 1} / ${images.length}`;
            };
            gallery.querySelector('.pending-gallery-previous').addEventListener('click', () => showImage(index - 1));
            gallery.querySelector('.pending-gallery-next').addEventListener('click', () => showImage(index + 1));
            const openLightbox = () => {
                activePendingImages = images;
                showPendingLightboxImage(index);
                pendingLightbox.showModal();
            };
            image.addEventListener('click', openLightbox);
            image.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openLightbox();
                }
            });
        });
        document.getElementById('pending-lightbox-previous').addEventListener('click', () => showPendingLightboxImage(activePendingIndex - 1));
        document.getElementById('pending-lightbox-next').addEventListener('click', () => showPendingLightboxImage(activePendingIndex + 1));
        document.getElementById('pending-lightbox-close').addEventListener('click', () => pendingLightbox.close());
        pendingLightbox.addEventListener('click', (event) => {
            if (event.target === pendingLightbox) pendingLightbox.close();
        });
        document.querySelectorAll('.pending-spot-map').forEach((element) => {
            const latitude = Number(element.dataset.latitude);
            const longitude = Number(element.dataset.longitude);
            const map = L.map(element).setView([latitude, longitude], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
            L.marker([latitude, longitude]).addTo(map).bindPopup(element.dataset.title);
        });
    </script>
<?php endif; ?>
