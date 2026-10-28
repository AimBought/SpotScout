<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/header.html.php'; ?>

    <main class="page">
        <section class="hero" aria-labelledby="welcome-heading">
            <div>
                <span class="radar-status">● LOCATION SCOUTING PLATFORM</span>
                <h1 id="welcome-heading">Discover &amp; scout breathtaking locations for photography and cinema.</h1>
                <p class="text-muted">
                    Find unique viewpoints, industrial halls, architecture, and nature spots verified by photographers, filmmakers, and explorers worldwide.
                </p>

                <div class="hero-actions">
                    <a href="/explore" class="btn btn-primary">Browse Explore Feed →</a>
                    <a href="/login" class="btn btn-outline">Log In</a>
                    <a href="/register" class="btn btn-outline">Create Account</a>
                </div>
            </div>

            <?php if (!empty($spots[0])): ?>
                <?php $featured = $spots[0]; ?>
                <aside class="spot-card" aria-label="Featured Spot Preview">
                    <div class="spot-card-visual">
                        <ul class="tag-list">
                            <li class="badge">SPOT PREVIEW</li>
                            <li class="badge"><?= htmlspecialchars($featured['category'], ENT_QUOTES, 'UTF-8') ?></li>
                        </ul>
                        <div class="visual-bottom">
                            <span class="photo-author">📍 <?= htmlspecialchars($featured['coordinates'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="saves-pill">★ <?= htmlspecialchars((string) $featured['rating'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </div>
                    <div class="spot-card-body">
                        <h2>
                            <a href="/spot/details/<?= (int) $featured['id'] ?>" class="spot-title-link">
                                <?= htmlspecialchars($featured['name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>
                        <p class="spot-meta"><?= htmlspecialchars($featured['location'], ENT_QUOTES, 'UTF-8') ?></p>
                        <a href="/spot/details/<?= (int) $featured['id'] ?>" class="details-link">Inspect Location →</a>
                    </div>
                </aside>
            <?php endif; ?>
        </section>

        <section class="stats-grid" aria-label="Community Statistics">
            <div class="stat-box">
                <span class="stat-value"><?= htmlspecialchars($stats['verifiedSpots'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="stat-label">Verified Spots</span>
            </div>
            <div class="stat-box">
                <span class="stat-value"><?= htmlspecialchars($stats['activeUsers'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="stat-label">Active Creators</span>
            </div>
            <div class="stat-box">
                <span class="stat-value"><?= htmlspecialchars($stats['droneFriendly'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="stat-label">Drone-Friendly Locations</span>
            </div>
        </section>

        <section class="features-grid" aria-label="Key Features">
            <article class="feature-box">
                <h2>📍 Verified GPS Telemetry</h2>
                <p class="text-muted">Exact coordinates, parking notes, and terrain access verified on site.</p>
            </article>
            <article class="feature-box">
                <h2>☀️ Golden Hour Tracking</h2>
                <p class="text-muted">Optimal lighting hours and sun direction tips shared by photographers.</p>
            </article>
            <article class="feature-box">
                <h2>🚁 Drone &amp; Tripod Rules</h2>
                <p class="text-muted">Clear badges for drone clearance, ground stability, and permit requirements.</p>
            </article>
        </section>

        <section aria-labelledby="preview-heading" style="margin-block: var(--space-4);">
            <div class="feed-header">
                <h2 id="preview-heading">Featured Locations</h2>
                <a href="/explore" class="details-link">View All in Explore →</a>
            </div>

            <div class="spots-grid">
                <?php foreach ($spots as $spot): ?>
                    <article class="spot-card">
                        <div class="spot-card-body">
                            <span class="badge" style="align-self: flex-start; margin-bottom: var(--space-1);">
                                <?= htmlspecialchars($spot['category'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <div class="spot-card-title-row">
                                <h3>
                                    <a href="/spot/details/<?= (int) $spot['id'] ?>" class="spot-title-link">
                                        <?= htmlspecialchars($spot['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h3>
                                <span class="rating-badge">★ <?= htmlspecialchars((string) $spot['rating'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <p class="spot-meta">
                                📍 <?= htmlspecialchars($spot['location'], ENT_QUOTES, 'UTF-8') ?> • <?= htmlspecialchars($spot['coordinates'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <a href="/spot/details/<?= (int) $spot['id'] ?>" class="details-link">Open Spot →</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <a href="/explore" class="mobile-nav-item">Explore</a>
        <a href="/spot/add" class="mobile-nav-item mobile-nav-add">+ Add</a>
        <a href="/login" class="mobile-nav-item">Log In</a>
    </nav>

    <footer class="page site-footer">
        <p>By continuing, you agree to SpotScout Community Guidelines &amp; Terms.</p>
        <p><a href="/about">About SpotScout</a></p>
    </footer>
</body>
</html>
