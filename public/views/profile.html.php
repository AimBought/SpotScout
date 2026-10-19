<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="page header-inner">
            <a href="/" class="brand-logo">
                SpotScout <span class="brand-dot" aria-hidden="true"></span>
            </a>
            <nav aria-label="Main navigation">
                <ul class="nav-list">
                    <li><a href="/explore">Explore Feed</a></li>
                    <li><a href="/spot/add" class="btn btn-primary">+ New Spot</a></li>
                    <li><a href="/login" class="btn btn-outline">Log Out</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="page">
        <section class="profile-header-card" aria-labelledby="profile-name">
            <div class="profile-top">
                <div class="profile-identity">
                    <div class="avatar-circle" aria-hidden="true">AK</div>
                    <div>
                        <h1 id="profile-name" style="margin-bottom: 0.15rem;">
                            <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>
                        </h1>
                        <p class="text-muted" style="margin: 0;">
                            <strong><?= htmlspecialchars($user['handle'], ENT_QUOTES, 'UTF-8') ?></strong> •
                            <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?> •
                            📍 <?= htmlspecialchars($user['location'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                </div>

                <div class="nav-list">
                    <button type="button" class="btn btn-primary">Edit Profile</button>
                    <button type="button" class="btn btn-outline">Share Portfolio</button>
                </div>
            </div>

            <p><?= htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8') ?></p>

            <div class="stats-grid" aria-label="User statistics">
                <div class="stat-box">
                    <span class="stat-value">⚡ <?= (int) $user['points'] ?></span>
                    <span class="stat-label">Scout Points</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value"><?= (int) $user['addedCount'] ?></span>
                    <span class="stat-label">Locations Added</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value"><?= htmlspecialchars($user['savesCount'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="stat-label">Total Saves</span>
                </div>
                <div class="stat-box">
                    <span class="stat-value"><?= htmlspecialchars($user['rank'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="stat-label">Community Rank</span>
                </div>
            </div>

            <div aria-label="Camera and drone gear">
                <span class="stat-label" style="display: block; margin-bottom: 0.4rem;">Prime Gear Locker:</span>
                <ul class="tag-list">
                    <?php foreach ($user['gear'] as $item): ?>
                        <li class="pin-chip">📷 <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>

        <section aria-labelledby="my-spots-heading" style="margin-bottom: var(--space-4);">
            <header class="feed-header">
                <h2 id="my-spots-heading" style="margin: 0;">My Added Spots (<?= count($mySpots) ?>)</h2>
                <nav class="sort-bar" aria-label="Spot status tabs">
                    <a href="/profile?tab=published" class="sort-link is-active">Published (<?= count($mySpots) ?>)</a>
                    <a href="/profile?tab=drafts" class="sort-link">Drafts (2)</a>
                    <a href="/profile?tab=saved" class="sort-link">Bookmarked</a>
                </nav>
            </header>

            <div class="spots-grid">
                <?php foreach ($mySpots as $spot): ?>
                    <article class="spot-card">
                        <div class="spot-card-visual">
                            <ul class="tag-list">
                                <?php foreach ($spot['badges'] as $badge): ?>
                                    <li class="badge"><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="visual-bottom">
                                <span class="photo-author">☀️ <?= htmlspecialchars($spot['bestLight'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="saves-pill">♥ <?= (int) $spot['saves'] ?></span>
                            </div>
                        </div>

                        <div class="spot-card-body">
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
                            <footer class="spot-card-footer">
                                <span class="light-tag"><?= (int) $spot['reviewsCount'] ?> reviews</span>
                                <a href="/spot/details/<?= (int) $spot['id'] ?>" class="details-link">Manage Spot →</a>
                            </footer>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <a href="/explore" class="mobile-nav-item">Explore</a>
        <a href="/spot/add" class="mobile-nav-item mobile-nav-add">+ Add</a>
        <a href="/profile" class="mobile-nav-item is-active">Profile</a>
    </nav>

    <footer class="page site-footer">
        <p>&copy; 2026 SpotScout.</p>
    </footer>
</body>
</html>