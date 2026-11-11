<section class="pending-contribution-section" aria-labelledby="pending-contributions-heading">
    <header class="feed-header">
        <div>
            <h2 id="pending-contributions-heading">Oczekujące zdjęcia</h2>
            <p class="text-muted">Sprawdź zdjęcia w kontekście miejsca, do którego zostały dodane.</p>
        </div>
    </header>
    <?php if ($pendingImageContributionGroups === []): ?>
        <p class="content-card text-muted">Brak zdjęć oczekujących na moderację.</p>
    <?php else: ?>
        <div class="pending-spot-list">
            <?php foreach ($pendingImageContributionGroups as $group): ?>
                <?php $spot = $group['spot']; ?>
                <article class="content-card pending-spot-card pending-photo-spot-card">
                    <div class="pending-spot-details">
                        <div>
                            <h3><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="text-muted">Spot dodany przez <?= htmlspecialchars($spot->getAuthorName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></p>
                            <?php if ($spot->getCategories() !== []): ?>
                                <ul class="tag-list"><?php foreach ($spot->getCategories() as $category): ?><li class="badge"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
                            <?php endif; ?>
                            <?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?>
                                <p class="text-muted">Lokalizacja: <?= htmlspecialchars(number_format($spot->getLatitude(), 6, '.', '') . ', ' . number_format($spot->getLongitude(), 6, '.', ''), ENT_QUOTES, 'UTF-8') ?></p>
                            <?php endif; ?>
                            <a class="btn btn-outline" href="/spot/details/<?= (int) $spot->getId() ?>">Otwórz stronę spotu</a>
                        </div>
                        <?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?>
                            <div id="pending-photo-map-<?= (int) $spot->getId() ?>" class="pending-spot-map" data-latitude="<?= htmlspecialchars((string) $spot->getLatitude(), ENT_QUOTES, 'UTF-8') ?>" data-longitude="<?= htmlspecialchars((string) $spot->getLongitude(), ENT_QUOTES, 'UTF-8') ?>" data-title="<?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>" aria-label="Mapa lokalizacji spotu"></div>
                        <?php else: ?>
                            <p class="text-muted">Lokalizacja nie została podana.</p>
                        <?php endif; ?>
                    </div>
                    <div class="pending-contribution-images">
                        <?php foreach ($group['images'] as $contribution): ?>
                            <article class="pending-contribution-item">
                                <button class="pending-contribution-image-button" type="button" data-image-src="<?= htmlspecialchars($contribution['image_path'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Powiększ zdjęcie">
                                    <img class="pending-contribution-image" src="<?= htmlspecialchars($contribution['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie zgłoszone do spotu <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                                </button>
                                <p class="text-muted">Zdjęcie dodane przez <?= htmlspecialchars($contribution['contributor_name'] ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="moderation-actions">
                                    <form action="/moderate/image/approve" method="post">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="image_id" value="<?= $contribution['id'] ?>">
                                        <button class="btn btn-primary" type="submit">Zatwierdź zdjęcie</button>
                                    </form>
                                    <form action="/moderate/image/reject" method="post">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="image_id" value="<?= $contribution['id'] ?>">
                                        <button class="btn btn-danger" type="submit">Odrzuć zdjęcie</button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <dialog class="pending-contribution-lightbox" id="pending-contribution-lightbox" aria-label="Powiększone zdjęcie">
            <button class="btn btn-outline" type="button" id="pending-contribution-lightbox-close">Zamknij ✕</button>
            <img id="pending-contribution-lightbox-image" alt="Powiększone zdjęcie">
        </dialog>
        <script>
            document.querySelectorAll('.pending-photo-spot-card .pending-spot-map').forEach((element) => {
                const latitude = Number(element.dataset.latitude);
                const longitude = Number(element.dataset.longitude);
                const map = L.map(element).setView([latitude, longitude], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
                L.marker([latitude, longitude]).addTo(map).bindPopup(document.createTextNode(element.dataset.title));
            });
            const contributionLightbox = document.getElementById('pending-contribution-lightbox');
            document.querySelectorAll('.pending-contribution-image-button').forEach((button) => button.addEventListener('click', () => {
                document.getElementById('pending-contribution-lightbox-image').src = button.dataset.imageSrc;
                contributionLightbox.showModal();
            }));
            document.getElementById('pending-contribution-lightbox-close').addEventListener('click', () => contributionLightbox.close());
            contributionLightbox.addEventListener('click', (event) => { if (event.target === contributionLightbox) contributionLightbox.close(); });
        </script>
    <?php endif; ?>
</section>
