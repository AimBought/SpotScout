<?php
?>
<style>
    .report-location-map{height:20rem;margin:.75rem 0 1rem;border:1px solid var(--color-border);border-radius:var(--radius-sm);overflow:hidden}
    .report-category-fieldset{min-width:0;margin:0;padding:.85rem;border:1px solid var(--color-border);border-radius:var(--radius-sm)}
    .report-category-options{display:grid;grid-template-columns:repeat(auto-fit,minmax(11rem,1fr));gap:.5rem}
    .report-category-options label{display:flex!important;align-items:center;justify-content:flex-start;gap:.6rem;padding:.55rem .7rem;border:1px solid var(--color-border);border-radius:var(--radius-sm);cursor:pointer}
    .report-category-options input[type=checkbox]{width:1rem!important;height:1rem;flex:0 0 auto;margin:0}
    .report-selected-photos{display:grid;grid-template-columns:repeat(auto-fill,minmax(10rem,1fr));gap:.7rem}
    .report-selected-photos figure{margin:0;padding:.5rem;border:1px solid var(--color-border);border-radius:var(--radius-sm)}
    .report-selected-photos img{display:block;width:100%;height:8rem;object-fit:cover;border-radius:.35rem}
    .report-selected-photos figcaption{margin-top:.4rem;color:var(--color-muted);font-size:.85rem}
</style>
<?php
$renderReportCard = static function (\App\Model\Report $report, bool $escalated = false) use ($csrfToken, $reportImages): void {
    $typeLabels = ['category' => 'Kategoria', 'location' => 'Lokalizacja', 'photo' => 'Zdjęcie', 'delete' => 'Usunięcie spotu'];
    $photos = $reportImages[$report->getId()] ?? [];
    $selectedPhotoKeys = $report->getProposedPhotoKeys();
    if ($report->getReportType() === 'photo' && $selectedPhotoKeys !== []) {
        $photos = array_values(array_filter($photos, static fn (array $photo): bool => in_array($photo['is_cover'] ? 'cover' : (string) $photo['image_id'], $selectedPhotoKeys, true)));
    }
    ?>
    <article class="report-card">
        <p class="eyebrow-label">Zgłoszenie #<?= $report->getId() ?> · <?= htmlspecialchars($typeLabels[$report->getReportType()] ?? $report->getReportType(), ENT_QUOTES, 'UTF-8') ?></p>
        <h3><a href="/spot/details/<?= $report->getSpotId() ?>"><?= htmlspecialchars($report->getSpotTitle(), ENT_QUOTES, 'UTF-8') ?></a></h3>
        <p class="text-muted">Zgłosił: <?= htmlspecialchars($report->getReporterName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($report->getCreatedAt()->format('d.m.Y H:i'), ENT_QUOTES, 'UTF-8') ?></p>
        <p><?= nl2br(htmlspecialchars($report->getDescription(), ENT_QUOTES, 'UTF-8')) ?></p>
        <?php if ($report->getLatitude() !== null && $report->getLongitude() !== null): ?><p class="text-muted">Lokalizacja: <?= htmlspecialchars(number_format($report->getLatitude(), 6, '.', '') . ', ' . number_format($report->getLongitude(), 6, '.', ''), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <?php if ($report->getReportType() === 'location' && $report->getProposedLatitude() !== null && $report->getProposedLongitude() !== null): ?>
            <p class="text-muted">Proponowana lokalizacja: <?= htmlspecialchars(number_format($report->getProposedLatitude(), 6, '.', '') . ', ' . number_format($report->getProposedLongitude(), 6, '.', ''), ENT_QUOTES, 'UTF-8') ?></p>
            <div class="report-location-map" id="report-location-map-<?= $report->getId() ?>" aria-label="Mapa obecnej i proponowanej lokalizacji"></div>
        <?php endif; ?>
        <?php if ($report->getReportType() === 'delete' && !$escalated && ($_SESSION['user_role'] ?? null) === 'moderator'): ?>
            <form action="/reports/escalate" method="post"><input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="report_id" value="<?= $report->getId() ?>"><button class="btn btn-primary" type="submit">Eskaluj do Admina</button></form>
        <?php elseif ($report->getReportType() === 'delete' && ($_SESSION['user_role'] ?? null) === 'admin'): ?>
            <form class="report-actions" action="/reports/delete-spot" method="post"><input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="report_id" value="<?= $report->getId() ?>"><label>Wiadomość dla właściciela<textarea name="owner_message" maxlength="1000" required></textarea></label><button class="btn btn-danger" type="submit">Usuń spot</button></form>
        <?php elseif (in_array($report->getReportType(), ['category', 'location', 'photo'], true)): ?>
            <form class="report-actions" action="/reports/resolve" method="post">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="report_id" value="<?= $report->getId() ?>">
                <?php if ($report->getReportType() === 'category'): ?><fieldset class="report-category-fieldset"><legend>Nowe kategorie</legend><div class="report-category-options"><?php $currentCategories = json_decode($report->getCategory() ?? '[]', true) ?: []; foreach (['Architecture', 'Industrial', 'Urban', 'Nature', 'Drone Friendly', 'Night'] as $category): ?><label><input type="checkbox" name="categories[]" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($category, $currentCategories, true) ? 'checked' : '' ?>><span><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
                <?php elseif ($report->getReportType() === 'location'): ?><p class="text-muted">Zatwierdzenie zastosuje lokalizację wskazaną pinezką przez zgłaszającego.</p>
                <?php else: ?><?php if ($photos === []): ?><p class="text-muted">To zgłoszenie nie zawiera już dostępnych zdjęć do weryfikacji.</p><?php else: ?><p>Zdjęcia zgłoszone do usunięcia:</p><div class="report-selected-photos"><?php foreach ($photos as $photo): ?><figure><img src="<?= htmlspecialchars($photo['path'], ENT_QUOTES, 'UTF-8') ?>" alt="Zdjęcie wskazane do usunięcia" loading="lazy"><figcaption><?= $photo['is_cover'] ? 'Zdjęcie główne' : 'Zdjęcie #' . (int) $photo['image_id'] ?></figcaption></figure><?php endforeach; ?></div><?php endif; ?><?php endif; ?>
                <label>Wiadomość dla właściciela<textarea name="owner_message" maxlength="1000" required></textarea></label><button class="btn btn-primary" type="submit" <?= $report->getReportType() === 'photo' && $photos === [] ? 'disabled' : '' ?>><?= $report->getReportType() === 'photo' ? 'Usuń wskazane zdjęcia i zamknij' : 'Zastosuj i zamknij zgłoszenie' ?></button>
            </form>
        <?php endif; ?>
        <?php if (!$escalated || ($_SESSION['user_role'] ?? null) === 'admin'): ?><form class="report-actions" action="/reports/reject" method="post"><input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="report_id" value="<?= $report->getId() ?>"><label><?= $report->getReportType() === 'photo' ? 'Powód pozostawienia zdjęć' : 'Powód odrzucenia' ?><textarea name="owner_message" maxlength="1000" required></textarea></label><button class="btn btn-outline" type="submit"><?= $report->getReportType() === 'photo' ? 'Pozostaw zdjęcia i odrzuć zgłoszenie' : 'Odrzuć zgłoszenie' ?></button></form><?php endif; ?>
    </article>
    <?php
};
?>
<section aria-labelledby="reports-heading"><header class="feed-header"><div><h2 id="reports-heading">Zgłoszenia</h2><p class="text-muted">Rozpatruj zgłoszenia użytkowników dotyczące spotów.</p></div></header>
    <?php if ($pendingReports === []): ?><div class="content-card text-muted">Brak zgłoszeń oczekujących na rozpatrzenie.</div><?php else: ?><div class="reports-grid"><?php foreach ($pendingReports as $report) { $renderReportCard($report); } ?></div><?php endif; ?>
</section>
<?php if (($_SESSION['user_role'] ?? null) === 'admin'): ?><section aria-labelledby="escalated-reports-heading"><header class="feed-header"><div><h2 id="escalated-reports-heading">Eskalowane do administratora</h2></div></header><?php if ($escalatedReports === []): ?><div class="content-card text-muted">Brak eskalowanych zgłoszeń.</div><?php else: ?><div class="reports-grid"><?php foreach ($escalatedReports as $report) { $renderReportCard($report, true); } ?></div><?php endif; ?></section><?php endif; ?>
<script>
const reportLocationMaps = <?= json_encode(array_values(array_map(static function (\App\Model\Report $report): array {
    if ($report->getReportType() !== 'location' || $report->getProposedLatitude() === null || $report->getProposedLongitude() === null) { return []; }
    return ['id' => $report->getId(), 'current' => [$report->getLatitude(), $report->getLongitude()], 'proposed' => [$report->getProposedLatitude(), $report->getProposedLongitude()]];
}, [...$pendingReports, ...$escalatedReports])), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?>.filter(Boolean);
for (const report of reportLocationMaps) {
    const map = L.map(`report-location-map-${report.id}`).setView(report.proposed, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19, attribution: '&copy; OpenStreetMap'}).addTo(map);
    const bounds = [report.proposed];
    L.marker(report.proposed).addTo(map).bindPopup('Proponowana lokalizacja');
    if (report.current[0] !== null && report.current[1] !== null) {
        L.marker(report.current).addTo(map).bindPopup('Obecna lokalizacja');
        bounds.push(report.current);
    }
    map.fitBounds(bounds, {padding: [28, 28], maxZoom: 15});
}
</script>
