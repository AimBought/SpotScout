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
            <nav aria-label="Breadcrumb navigation">
                <ul class="nav-list">
                    <li><a href="/explore">← Back to Explore</a></li>
                    <li><a href="/spot/add" class="btn btn-primary">+ New Spot</a></li>
                    <li><a href="/profile" class="profile-pill">@adam_k</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="page">
        <article>
            <header class="spot-hero-banner">
                <ul class="tag-list" aria-label="Categories and parameters">
                    <?php foreach ($spot['badges'] as $badge): ?>
                        <li class="badge"><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>

                <h1><?= htmlspecialchars($spot['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($spot['subtitle'], ENT_QUOTES, 'UTF-8') ?>)</h1>

                <p class="spot-hero-meta">
                    <span class="rating-badge">★ <?= htmlspecialchars((string) $spot['rating'], ENT_QUOTES, 'UTF-8') ?></span>
                    (<?= (int) $spot['reviewsCount'] ?> reviews) •
                    📍 <?= htmlspecialchars($spot['location'], ENT_QUOTES, 'UTF-8') ?> •
                    Photo by <?= htmlspecialchars($spot['author'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </header>

            <div class="details-layout">
                <div>
                    <section class="content-card" aria-labelledby="location-info-heading">
                        <h2 id="location-info-heading">Verified Coordinates &amp; Practical Info</h2>
                        <p><?= htmlspecialchars($spot['description'], ENT_QUOTES, 'UTF-8') ?></p>

                        <dl class="info-grid">
                            <div class="info-tile">
                                <dt>GPS Coordinates</dt>
                                <dd><?= htmlspecialchars($spot['coordinates'], ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>
                            <div class="info-tile">
                                <dt>Best Lighting Window</dt>
                                <dd><?= htmlspecialchars($spot['bestLight'], ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>
                            <div class="info-tile">
                                <dt>Distance</dt>
                                <dd><?= htmlspecialchars($spot['distance'], ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>
                            <div class="info-tile">
                                <dt>Access &amp; Parking</dt>
                                <dd><?= htmlspecialchars($spot['accessInfo'], ENT_QUOTES, 'UTF-8') ?></dd>
                            </div>
                        </dl>

                        <div class="hero-actions">
                            <button type="button" class="btn btn-primary">Navigate to Spot</button>
                            <button type="button" class="btn btn-outline">♥ Save Spot (<?= (int) $spot['saves'] ?>)</button>
                        </div>
                    </section>
                </div>

                <aside>
                    <section class="content-card" aria-labelledby="notes-heading">
                        <div class="form-row-between" style="margin-bottom: var(--space-2);">
                            <h2 id="notes-heading" style="margin: 0;">Community Notes &amp; Gear Logs</h2>
                            <span class="badge"><?= count($spot['notes']) ?> Logs</span>
                        </div>

                        <?php if (empty($spot['notes'])): ?>
                            <p class="text-muted">No community notes yet for this location. Be the first to share your tips!</p>
                        <?php else: ?>
                            <?php foreach ($spot['notes'] as $note): ?>
                                <article class="review-card">
                                    <header class="review-header">
                                        <div>
                                            <strong><?= htmlspecialchars($note['author'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <span class="text-muted">(<?= htmlspecialchars($note['role'], ENT_QUOTES, 'UTF-8') ?>)</span>
                                        </div>
                                        <span class="rating-badge">★ <?= htmlspecialchars((string) $note['rating'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </header>
                                    <p style="margin-bottom: 0.5rem;"><?= htmlspecialchars($note['comment'], ENT_QUOTES, 'UTF-8') ?></p>
                                    <footer class="form-row-between text-muted" style="font-size: 0.8rem;">
                                        <span>📷 <?= htmlspecialchars($note['gear'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <time><?= htmlspecialchars($note['date'], ENT_QUOTES, 'UTF-8') ?></time>
                                    </footer>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </section>

                    <section class="content-card" aria-labelledby="add-review-heading">
                        <h2 id="add-review-heading">Add Your Note &amp; Rating</h2>
                        <form action="/spot/details/<?= (int) $spot['id'] ?>" method="post" class="form-stack">
                            <div class="form-group">
                                <label for="rating" class="form-label-row">Rating (1-5)</label>
                                <select id="rating" name="rating" class="form-select">
                                    <option value="5">★★★★★ (5.0 - Excellent)</option>
                                    <option value="4">★★★★☆ (4.0 - Good)</option>
                                    <option value="3">★★★☆☆ (3.0 - Average)</option>
                                    <option value="2">★★☆☆☆ (2.0 - Poor)</option>
                                    <option value="1">★☆☆☆☆ (1.0 - Avoid)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="gear" class="form-label-row">Camera / Drone Used</label>
                                <input type="text" id="gear" name="gear" class="form-input" placeholder="e.g. Sony A7 IV 35mm, DJI Mini 4">
                            </div>

                            <div class="form-group">
                                <label for="comment" class="form-label-row">Scouting Notes</label>
                                <textarea id="comment" name="comment" rows="3" class="form-textarea" placeholder="Share lighting tips, parking info, or drone conditions..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">Submit Field Review →</button>
                        </form>
                    </section>
                </aside>
            </div>
        </article>
    </main>

    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <a href="/explore" class="mobile-nav-item">Explore</a>
        <a href="/spot/add" class="mobile-nav-item mobile-nav-add">+ Add</a>
        <a href="/profile" class="mobile-nav-item">Profile</a>
    </nav>

    <footer class="page site-footer">
        <p>&copy; 2026 SpotScout.</p>
    </footer>
</body>
</html>