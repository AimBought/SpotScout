<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <header>
        <a href="/"><strong>SpotScout</strong></a>
        <nav aria-label="Breadcrumb">
            <a href="/explore">← Back to Explore</a>
        </nav>
    </header>

    <main>
        <article>
            <header>
                <ul aria-label="Categories and parameters">
                    <?php foreach ($spot['badges'] as $badge): ?>
                        <li><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>

                <h1><?= htmlspecialchars($spot['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($spot['subtitle'], ENT_QUOTES, 'UTF-8') ?>)</h1>

                <p>
                    <span>★ <?= htmlspecialchars((string) $spot['rating'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $spot['reviewsCount'] ?> reviews)</span> •
                    <span><?= htmlspecialchars($spot['location'], ENT_QUOTES, 'UTF-8') ?></span> •
                    <span>Photo by <?= htmlspecialchars($spot['author'], ENT_QUOTES, 'UTF-8') ?></span>
                </p>
            </header>

            <section aria-labelledby="location-info-heading">
                <h2 id="location-info-heading">Location &amp; Practical Info</h2>
                <p><?= htmlspecialchars($spot['description'], ENT_QUOTES, 'UTF-8') ?></p>
                <dl>
                    <dt>GPS Coordinates</dt>
                    <dd><?= htmlspecialchars($spot['coordinates'], ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt>Best Lighting</dt>
                    <dd><?= htmlspecialchars($spot['bestLight'], ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt>Access &amp; Parking</dt>
                    <dd><?= htmlspecialchars($spot['accessInfo'], ENT_QUOTES, 'UTF-8') ?></dd>
                </dl>
                <button type="button">Navigate to Spot</button>
                <button type="button">Save Spot (<?= (int) $spot['saves'] ?>)</button>
            </section>

            <section aria-labelledby="notes-heading">
                <h2 id="notes-heading">Community Notes &amp; Reviews</h2>

                <?php if (empty($spot['notes'])): ?>
                    <p>No community notes yet for this location. Be the first to add one!</p>
                <?php else: ?>
                    <?php foreach ($spot['notes'] as $note): ?>
                        <article>
                            <header>
                                <strong><?= htmlspecialchars($note['author'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span>(<?= htmlspecialchars($note['role'], ENT_QUOTES, 'UTF-8') ?>)</span> •
                                <time><?= htmlspecialchars($note['date'], ENT_QUOTES, 'UTF-8') ?></time> •
                                <span>Rating: ★ <?= htmlspecialchars((string) $note['rating'], ENT_QUOTES, 'UTF-8') ?></span>
                            </header>
                            <p><?= htmlspecialchars($note['comment'], ENT_QUOTES, 'UTF-8') ?></p>
                            <footer>
                                <small>Gear used: <?= htmlspecialchars($note['gear'], ENT_QUOTES, 'UTF-8') ?></small>
                            </footer>
                        </article>
                        <hr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section aria-labelledby="add-review-heading">
                <h2 id="add-review-heading">Add Your Note &amp; Rating</h2>
                <form action="/spot/details/<?= (int) $spot['id'] ?>" method="post">
                    <div>
                        <label for="rating">Rating (1-5)</label>
                        <select id="rating" name="rating">
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Poor</option>
                            <option value="1">1 - Avoid</option>
                        </select>
                    </div>
                    <div>
                        <label for="gear">Camera / Drone Used (optional)</label>
                        <input type="text" id="gear" name="gear" placeholder="e.g. Sony A7 IV 35mm, DJI Mini 4">
                    </div>
                    <div>
                        <label for="comment">Scouting Notes</label>
                        <textarea id="comment" name="comment" rows="4" placeholder="Share lighting tips, access info, or drone conditions..."></textarea>
                    </div>
                    <button type="submit">Submit Review</button>
                </form>
            </section>
        </article>
    </main>

    <footer>
        <p>&copy; 2026 SpotScout.</p>
    </footer>
</body>
</html>