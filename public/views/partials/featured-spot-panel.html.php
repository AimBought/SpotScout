<?php
$featuredImage = $featuredSpot?->getImagePath() ?? '/images/featured-spot.svg';
?>
<aside class="split-image" style="background-image: url('<?= htmlspecialchars($featuredImage, ENT_QUOTES, 'UTF-8') ?>')" aria-label="Wyróżniona lokalizacja SpotScout">
    <div class="spot-overlay-card">
        <?php if ($featuredSpot !== null): ?>
            <span class="spot-overlay-eyebrow">SPOT OF THE DAY</span>
            <h2><?= htmlspecialchars($featuredSpot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h2>
            <p>
                <?php if ($featuredSpot->getCategory() !== null): ?><?= htmlspecialchars($featuredSpot->getCategory(), ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
                <?php if ($featuredSpot->getCategory() !== null && $featuredSpot->getLatitude() !== null): ?> · <?php endif; ?>
                <?php if ($featuredSpot->getLatitude() !== null && $featuredSpot->getLongitude() !== null): ?><?= htmlspecialchars(number_format($featuredSpot->getLatitude(), 5, '.', '') . ', ' . number_format($featuredSpot->getLongitude(), 5, '.', ''), ENT_QUOTES, 'UTF-8') ?><?php endif; ?>
            </p>
            <a class="btn btn-primary" href="/spot/details/<?= (int) $featuredSpot->getId() ?>">Explore spot <span aria-hidden="true">→</span></a>
        <?php else: ?>
            <span class="spot-overlay-eyebrow">FIND YOUR NEXT FRAME</span>
            <h2>Your next cinematic location is waiting.</h2>
            <p>Discover inspiring places shared by photographers and filmmakers.</p>
            <a class="btn btn-primary" href="/explore">Explore locations <span aria-hidden="true">→</span></a>
        <?php endif; ?>
    </div>
</aside>
