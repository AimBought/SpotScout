<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <script>
        (function(){var t=localStorage.getItem('theme')||(window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLGPg8YJ04IY4YZ0WMx6Y0hSp4pMwDlkRzTFbGMPT4ec7Zua7+CQRkpw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/header.html.php'; ?>

    <main class="page explore-layout">
        <aside class="sidebar-panel" aria-label="Categories filter">
            <div class="sidebar-block">
                <span class="eyebrow-label">SPOTSCOUT STUDIO</span>
                <h2 class="sidebar-title">Categories</h2>
                <nav aria-label="Spot categories">
                    <ul class="category-list">
                        <?php foreach ($categories as $index => $category): ?>
                            <li>
                                <a
                                    href="/explore?category=<?= urlencode($category['name']) ?>"
                                    class="category-pill <?= $index === 0 ? 'is-active' : '' ?>"
                                >
                                    <span><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="category-count"><?= (int) $category['count'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <section class="feed-column" aria-labelledby="explore-heading">
            <header class="feed-header">
                <div>
                    <h1 id="explore-heading">Active Production Spots</h1>
                    <p class="text-muted">
                        Scouted viewpoints with verified coordinates, lighting hours, and drone rules (<?= (int) $totalSpots ?> total).
                    </p>
                </div>
                <div class="sort-bar" aria-label="Sort options">
                    <span class="text-muted">Sort:</span>
                    <a href="/explore?sort=trending" class="sort-link is-active">Trending</a>
                    <a href="/explore?sort=distance" class="sort-link">Distance</a>
                    <a href="/explore?sort=rating" class="sort-link">Top Rated</a>
                </div>
            </header>

            <section class="radar-card" aria-labelledby="map-preview-heading">
                <div class="radar-top">
                    <div>
                        <span class="radar-status">● RADAR • LIVE OPTIC MAP</span>
                        <h2 id="map-preview-heading" class="radar-title">Nearby Scouted Pins</h2>
                    </div>
                    <button type="button" class="btn btn-outline">Expand Map View</button>
                </div>
                <ul class="tag-list">
                    <?php foreach ($nearbyMapPins as $pin): ?>
                        <li class="pin-chip">
                            <span class="pin-dot" aria-hidden="true"></span>
                            <strong><?= htmlspecialchars($pin['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="text-muted"><?= htmlspecialchars($pin['distance'], ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="spots-grid" aria-label="Spots feed">
                <?php foreach ($spots as $spot): ?>
                    <article class="spot-card">
                        <div class="spot-card-visual">
                            <ul class="tag-list" aria-label="Spot tags">
                                <?php foreach ($spot['badges'] as $badge): ?>
                                    <li class="badge"><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="visual-bottom">
                                <span class="photo-author">Photo: <?= htmlspecialchars($spot['author'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="saves-pill">♥ <?= (int) $spot['saves'] ?></span>
                            </div>
                        </div>

                        <div class="spot-card-body">
                            <div class="spot-card-title-row">
                                <h2>
                                    <a href="/spot/details/<?= (int) $spot['id'] ?>" class="spot-title-link">
                                        <?= htmlspecialchars($spot['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h2>
                                <span class="rating-badge">
                                    ★ <?= htmlspecialchars((string) $spot['rating'], ENT_QUOTES, 'UTF-8') ?>
                                    <small class="text-muted">(<?= (int) $spot['reviewsCount'] ?>)</small>
                                </span>
                            </div>

                            <p class="spot-meta">
                                📍 <?= htmlspecialchars($spot['location'], ENT_QUOTES, 'UTF-8') ?>
                                • <?= htmlspecialchars($spot['distance'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p class="spot-description">
                                <?= htmlspecialchars($spot['description'], ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <footer class="spot-card-footer">
                                <span class="light-tag">☀️ <?= htmlspecialchars($spot['bestLight'], ENT_QUOTES, 'UTF-8') ?></span>
                                <a href="/spot/details/<?= (int) $spot['id'] ?>" class="details-link">View Spot →</a>
                            </footer>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        </section>
    </main>

    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <a href="/explore" class="mobile-nav-item is-active">Explore</a>
        <a href="/spot/add" class="mobile-nav-item mobile-nav-add">+ Add</a>
        <a href="/profile" class="mobile-nav-item">Profile</a>
    </nav>

    <footer class="page site-footer">
        <p>&copy; 2026 SpotScout. Community platform for photographers, filmmakers, and explorers.</p>
    </footer>
<script src="/js/theme.js" defer></script>
</body>
</html>
