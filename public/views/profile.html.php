<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
    <link rel="stylesheet" href="/css/style.css?v=profile-bookmarks-1">
    <style>
        .profile-avatar{width:8rem;height:8rem;object-fit:cover;border-radius:50%;border:4px solid var(--color-surface);box-shadow:var(--shadow-card)}
        .profile-avatar-trigger{display:block;border:0;border-radius:50%;padding:0;background:transparent;cursor:pointer;line-height:0}
        .profile-avatar-trigger:focus-visible{outline:3px solid var(--color-brand);outline-offset:4px}
        .profile-alert{padding:var(--space-2);border-radius:var(--radius-sm);background:var(--color-surface);border:1px solid var(--color-border);margin-bottom:var(--space-2)}
        .profile-spots .spot-card-link{display:flex;flex-direction:column;height:100%;color:inherit}
        .profile-spots .spot-card-visual.has-image{background-size:cover;background-position:center;min-height:13rem}
        .avatar-dialog{width:min(92vw,30rem);border:1px solid var(--color-border);border-radius:var(--radius);padding:var(--space-3);background:var(--color-surface);color:var(--color-text);box-shadow:var(--shadow-card)}
        .avatar-dialog::backdrop{background:rgba(15,23,42,.68)}
        .avatar-dialog h2{margin-top:0}
        .avatar-crop-canvas{display:block;width:min(100%,20rem);aspect-ratio:1;margin:var(--space-2) auto;background:var(--color-background);border:1px solid var(--color-border);border-radius:var(--radius-sm);touch-action:none;cursor:grab}
        .avatar-crop-canvas:active{cursor:grabbing}
        .avatar-zoom{display:grid;gap:.4rem;margin-bottom:var(--space-2)}
        .avatar-dialog-actions{display:flex;justify-content:flex-end;gap:var(--space-1);flex-wrap:wrap}
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/header.html.php'; ?>
<main class="page">
    <section class="profile-header-card" aria-labelledby="profile-name">
        <?php if ($errors !== []): ?><div class="profile-alert" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
        <?php if (($_GET['avatar'] ?? '') === 'updated'): ?><p class="profile-alert" role="status">Awatar został zaktualizowany.</p><?php endif; ?>
        <div class="profile-top">
            <div class="profile-identity">
                <button class="profile-avatar-trigger" id="open-avatar-editor" type="button" aria-label="Zmień awatar użytkownika">
                    <img class="profile-avatar" src="<?= htmlspecialchars($user->getAvatarPath() ?? '/images/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>" alt="Awatar użytkownika <?= htmlspecialchars($user->getName(), ENT_QUOTES, 'UTF-8') ?>" width="128" height="128">
                </button>
                <div><h1 id="profile-name" style="margin-bottom:.15rem"><?= htmlspecialchars($user->getName(), ENT_QUOTES, 'UTF-8') ?></h1><p class="text-muted" style="margin:0"><?= htmlspecialchars($user->getEmail(), ENT_QUOTES, 'UTF-8') ?></p></div>
            </div>
        </div>
        <p class="text-muted">Kliknij zdjęcie profilowe, aby wybrać i wykadrować nowy awatar.</p>
    </section>
    <section aria-labelledby="my-spots-heading" style="margin-bottom:var(--space-4)">
        <header class="feed-header"><div><h2 id="my-spots-heading" style="margin:0">Moje spoty (<?= count($spots) ?>)</h2><p class="text-muted">Miejsca dodane przez Ciebie.</p></div><a class="btn btn-outline" href="/spot/add">+ Dodaj spot</a></header>
        <?php if ($spots === []): ?><p class="content-card text-muted">Nie dodałeś jeszcze żadnego spotu.</p><?php endif; ?>
        <div class="spots-grid profile-spots">
            <?php foreach ($spots as $spot): ?>
                <?php $imagePath = $spot->getImagePath(); ?>
                <article class="spot-card"><a href="/spot/details/<?= (int) $spot->getId() ?>" class="spot-card-link">
                    <div class="spot-card-visual<?= $imagePath !== null ? ' has-image' : '' ?>"<?php if ($imagePath !== null): ?> style="background-image:url('<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>')"<?php endif; ?>><?php if ($spot->getCategory() !== null): ?><ul class="tag-list"><li class="badge"><?= htmlspecialchars($spot->getCategory(), ENT_QUOTES, 'UTF-8') ?></li></ul><?php endif; ?></div>
                    <div class="spot-card-body"><div class="spot-card-title-row"><h3 class="spot-title-link"><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3></div><p class="spot-meta"><?php if ($spot->getLatitude() !== null && $spot->getLongitude() !== null): ?><?= htmlspecialchars(number_format($spot->getLatitude(), 5, '.', '') . ', ' . number_format($spot->getLongitude(), 5, '.', ''), ENT_QUOTES, 'UTF-8') ?><?php else: ?>Brak współrzędnych<?php endif; ?></p><footer class="spot-card-footer"><span class="light-tag">Zobacz szczegóły</span><span class="details-link">Otwórz →</span></footer></div>
                </a>
                <form class="profile-spot-delete" action="/spot/delete/<?= (int) $spot->getId() ?>" method="post" onsubmit="return confirm('Czy na pewno chcesz usunąć ten spot? Tej operacji nie można cofnąć.');">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="spot-delete-icon" type="submit" aria-label="Delete spot: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>" title="Delete spot"><svg aria-hidden="true" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2m3 0-.9 14H5.9L5 6m4 4v6m6-6v6"/></svg></button>
                </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="profile-saved-spots" aria-labelledby="saved-spots-heading" style="margin-bottom:var(--space-4)">
        <header class="feed-header"><div><h2 id="saved-spots-heading" style="margin:0">Saved spots (<?= count($bookmarkedSpots) ?>)</h2><p class="text-muted">Lokacje, które zapisałeś na później.</p></div></header>
        <?php if ($bookmarkedSpots === []): ?><p class="content-card text-muted">Nie masz jeszcze zapisanych spotów. Użyj ikony zakładki na kafelku lub stronie spotu.</p><?php endif; ?>
        <div class="spots-grid profile-spots">
            <?php foreach ($bookmarkedSpots as $spot): ?>
                <?php $imagePath = $spot->getImagePath(); ?>
                <article class="spot-card"><a href="/spot/details/<?= (int) $spot->getId() ?>" class="spot-card-link">
                    <div class="spot-card-visual<?= $imagePath !== null ? ' has-image' : '' ?>"<?php if ($imagePath !== null): ?> style="background-image:url('<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>')"<?php endif; ?>><?php if ($spot->getCategory() !== null): ?><ul class="tag-list"><li class="badge"><?= htmlspecialchars($spot->getCategory(), ENT_QUOTES, 'UTF-8') ?></li></ul><?php endif; ?></div>
                    <div class="spot-card-body"><div class="spot-card-title-row"><h3 class="spot-title-link"><?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3></div><p class="spot-meta"><?= htmlspecialchars($spot->getAuthorName() ?? 'Użytkownik', ENT_QUOTES, 'UTF-8') ?></p><footer class="spot-card-footer"><span class="light-tag">Szczegóły</span><span class="details-link">Otwórz →</span></footer></div>
                </a>
                <form class="spot-bookmark-form" action="/spot/bookmark/<?= (int) $spot->getId() ?>" method="post">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="return_to" value="/profile">
                    <button class="spot-bookmark-button is-bookmarked" type="submit" aria-label="Usuń zakładkę: <?= htmlspecialchars($spot->getTitle(), ENT_QUOTES, 'UTF-8') ?>" aria-pressed="true" title="Usuń z zapisanych"><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.75h12v16.5l-6-4-6 4z"/></svg></button>
                </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<dialog class="avatar-dialog" id="avatar-dialog" aria-labelledby="avatar-dialog-title">
    <form action="/profile/avatar" method="post" enctype="multipart/form-data" id="avatar-form">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        <h2 id="avatar-dialog-title">Dostosuj awatar</h2>
        <p class="text-muted">Przeciągnij zdjęcie, aby ustawić kadr, i dostosuj powiększenie.</p>
        <input type="file" id="avatar-file" name="avatar" accept="image/jpeg,image/png" required hidden>
        <button type="button" id="choose-avatar-file" class="btn btn-outline">Wybierz zdjęcie</button>
        <canvas class="avatar-crop-canvas" id="avatar-crop-canvas" width="320" height="320" aria-label="Podgląd przyciętego awatara"></canvas>
        <label class="avatar-zoom" for="avatar-zoom">Powiększenie <input type="range" id="avatar-zoom" min="1" max="3" step="0.01" value="1" disabled></label>
        <div class="avatar-dialog-actions"><button type="button" class="btn btn-outline" id="cancel-avatar-edit">Anuluj</button><button type="submit" class="btn btn-primary" id="save-avatar" disabled>Zatwierdź zdjęcie</button></div>
    </form>
</dialog>
<nav class="mobile-bottom-nav" aria-label="Nawigacja mobilna"><a href="/explore" class="mobile-nav-item">Explore</a><a href="/spot/add" class="mobile-nav-item mobile-nav-add">+ Dodaj</a><a href="/profile" class="mobile-nav-item is-active">Profil</a></nav>
<footer class="page site-footer"><p>&copy; 2026 SpotScout.</p></footer>
<script src="/js/theme.js" defer></script>
<script>
const avatarDialog = document.getElementById('avatar-dialog');
const avatarFileInput = document.getElementById('avatar-file');
const avatarCanvas = document.getElementById('avatar-crop-canvas');
const avatarContext = avatarCanvas.getContext('2d');
const avatarZoom = document.getElementById('avatar-zoom');
const saveAvatarButton = document.getElementById('save-avatar');
let sourceImage = null;
let crop = { x: 0, y: 0, side: 0 };
let dragPoint = null;

function clampCrop() {
    const maxX = Math.max(0, sourceImage.naturalWidth - crop.side);
    const maxY = Math.max(0, sourceImage.naturalHeight - crop.side);
    crop.x = Math.max(0, Math.min(maxX, crop.x));
    crop.y = Math.max(0, Math.min(maxY, crop.y));
}

function drawAvatarCrop() {
    if (!sourceImage) return;
    clampCrop();
    avatarContext.clearRect(0, 0, avatarCanvas.width, avatarCanvas.height);
    avatarContext.drawImage(sourceImage, crop.x, crop.y, crop.side, crop.side, 0, 0, avatarCanvas.width, avatarCanvas.height);
}

function chooseAvatarImage() {
    avatarFileInput.value = '';
    avatarFileInput.click();
}
document.getElementById('open-avatar-editor').addEventListener('click', () => {
    avatarDialog.showModal();
    chooseAvatarImage();
});
document.getElementById('choose-avatar-file').addEventListener('click', chooseAvatarImage);
document.getElementById('cancel-avatar-edit').addEventListener('click', () => avatarDialog.close());
avatarFileInput.addEventListener('change', () => {
    const file = avatarFileInput.files?.[0];
    if (!file) return;
    sourceImage = null;
    saveAvatarButton.disabled = true;
    avatarZoom.disabled = true;
    const imageUrl = URL.createObjectURL(file);
    const image = new Image();
    image.onload = () => {
        if (sourceImage?.src.startsWith('blob:')) URL.revokeObjectURL(sourceImage.src);
        sourceImage = image;
        crop.side = Math.min(image.naturalWidth, image.naturalHeight);
        crop.x = (image.naturalWidth - crop.side) / 2;
        crop.y = (image.naturalHeight - crop.side) / 2;
        avatarZoom.value = '1';
        avatarZoom.disabled = false;
        saveAvatarButton.disabled = false;
        drawAvatarCrop();
    };
    image.onerror = () => URL.revokeObjectURL(imageUrl);
    image.src = imageUrl;
});
avatarZoom.addEventListener('input', () => {
    if (!sourceImage) return;
    const centerX = crop.x + crop.side / 2;
    const centerY = crop.y + crop.side / 2;
    crop.side = Math.min(sourceImage.naturalWidth, sourceImage.naturalHeight) / Number(avatarZoom.value);
    crop.x = centerX - crop.side / 2;
    crop.y = centerY - crop.side / 2;
    drawAvatarCrop();
});
avatarCanvas.addEventListener('pointerdown', event => {
    if (!sourceImage) return;
    dragPoint = { x: event.clientX, y: event.clientY };
    avatarCanvas.setPointerCapture(event.pointerId);
});
avatarCanvas.addEventListener('pointermove', event => {
    if (!sourceImage || !dragPoint) return;
    const rect = avatarCanvas.getBoundingClientRect();
    const scale = crop.side / rect.width;
    crop.x -= (event.clientX - dragPoint.x) * scale;
    crop.y -= (event.clientY - dragPoint.y) * scale;
    dragPoint = { x: event.clientX, y: event.clientY };
    drawAvatarCrop();
});
avatarCanvas.addEventListener('pointerup', () => { dragPoint = null; });
avatarCanvas.addEventListener('pointercancel', () => { dragPoint = null; });
const avatarForm = document.getElementById('avatar-form');
avatarForm.addEventListener('submit', event => {
    if (!sourceImage) return;
    event.preventDefault();
    saveAvatarButton.disabled = true;
    avatarCanvas.toBlob(blob => {
        if (!blob) {
            saveAvatarButton.disabled = false;
            return;
        }
        const croppedFile = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
        const transfer = new DataTransfer();
        transfer.items.add(croppedFile);
        avatarFileInput.files = transfer.files;
        avatarForm.submit();
    }, 'image/jpeg', 0.92);
});
</script>
</body>
</html>
