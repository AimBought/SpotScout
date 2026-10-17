<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <header>
        <div>
            <a href="/"><strong>SpotScout</strong></a>
        </div>

        <form action="/explore" method="get" role="search">
            <label for="search-spots">Search spots</label>
            <input
                type="search"
                id="search-spots"
                name="q"
                placeholder="Search spots, cities, tags..."
            >
            <button type="submit">Search</button>
        </form>

        <nav aria-label="Main navigation">
            <ul>
                <li><a href="/">Welcome</a></li>
                <li><a href="/explore" aria-current="page">Explore Feed</a></li>
                <li><a href="/spot/add">+ New Spot</a></li>
                <li><a href="/profile">@adam_k</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <aside aria-label="Categories filter">
            <h2>Categories</h2>
            <nav aria-label="Spot categories">
                <ul>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="/explore?category=<?= urlencode($category['name']) ?>">
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                                (<?= (int) $category['count'] ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>

        <section aria-labelledby="explore-heading">
            <header>
                <h1 id="explore-heading">Explore Spots (<?= (int) $totalSpots ?>)</h1>
                <p>Discover unique and verified locations for photo shoots, video production, and sightseeing.</p>
                <div aria-label="Sort options">
                    <span>Sort by:</span>
                    <a href="/explore?sort=trending">Trending</a> |
                    <a href="/explore?sort=distance">Distance</a> |
                    <a href="/explore?sort=rating">Top Rated</a>
                </div>
            </header>

            <section aria-labelledby="map-preview-heading">
                <h2 id="map-preview-heading">Interactive Map Preview</h2>
                <p>Nearby pins on map:</p>
                <ul>
                    <?php foreach ($nearbyMapPins as $pin): ?>
                        <li>
                            <strong><?= htmlspecialchars($pin['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                            (<?= htmlspecialchars($pin['distance'], ENT_QUOTES, 'UTF-8') ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button type="button">Expand Map View</button>
            </section>

            <section aria-label="Spots feed">
                <?php foreach ($spots as $spot): ?>
                    <article>
                        <header>
                            <ul aria-label="Spot tags">
                                <?php foreach ($spot['badges'] as $badge): ?>
                                    <li><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <h2>
                                <a href="/spot/details/<?= (int) $spot['id'] ?>">
                                    <?= htmlspecialchars($spot['name'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </h2>

                            <p>
                                <span><?= htmlspecialchars($spot['location'], ENT_QUOTES, 'UTF-8') ?></span>
                                • <span><?= htmlspecialchars($spot['distance'], ENT_QUOTES, 'UTF-8') ?></span>
                                • <span>Photo: <?= htmlspecialchars($spot['author'], ENT_QUOTES, 'UTF-8') ?></span>
                            </p>
                        </header>

                        <p><?= htmlspecialchars($spot['description'], ENT_QUOTES, 'UTF-8') ?></p>

                        <footer>
                            <span>★ <?= htmlspecialchars((string) $spot['rating'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $spot['reviewsCount'] ?>)</span>
                            • <span>♥ <?= (int) $spot['saves'] ?> saves</span>
                            • <a href="/spot/details/<?= (int) $spot['id'] ?>">View Spot Details →</a>
                        </footer>
                    </article>
                    <hr>
                <?php endforeach; ?>
            </section>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 SpotScout. Community platform for photographers, filmmakers, and explorers.</p>
    </footer>
</body>
</html>