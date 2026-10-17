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
        <nav aria-label="Welcome navigation">
            <ul>
                <li><a href="/explore">Explore Feed</a></li>
                <li><a href="/login">Log In</a></li>
                <li><a href="/register">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section aria-labelledby="welcome-heading">
            <h1 id="welcome-heading">Discover &amp; scout breathtaking locations for photography and cinema.</h1>
            <p>Find unique viewpoints, industrial halls, architecture, and nature spots verified by creators worldwide.</p>

            <div>
                <a href="/login"><button type="button">Log In</button></a>
                <a href="/register"><button type="button">Sign Up</button></a>
                <a href="/explore">Browse Explore Feed →</a>
            </div>
        </section>

        <section aria-label="Key Features">
            <h2>Platform Features</h2>
            <ul>
                <li><strong>Verified GPS Locations</strong> – Exact coordinates and access instructions.</li>
                <li><strong>Golden Hour &amp; Lighting Info</strong> – Best time of day for shooting.</li>
                <li><strong>Drone &amp; Gear Friendly</strong> – Clear tags for drone rules and tripod stability.</li>
            </ul>
        </section>

        <section aria-label="Community Statistics">
            <h2>SpotScout Community in Numbers</h2>
            <ul>
                <li>Verified Spots: <strong><?= htmlspecialchars($stats['verifiedSpots'], ENT_QUOTES, 'UTF-8') ?></strong></li>
                <li>Active Creators: <strong><?= htmlspecialchars($stats['activeUsers'], ENT_QUOTES, 'UTF-8') ?></strong></li>
                <li>Drone-Friendly Spots: <strong><?= htmlspecialchars($stats['droneFriendly'], ENT_QUOTES, 'UTF-8') ?></strong></li>
            </ul>
        </section>

        <section aria-labelledby="preview-heading">
            <h2 id="preview-heading">Featured Spot Preview</h2>
            <?php foreach ($spots as $spot): ?>
                <article>
                    <h3>
                        <a href="/spot/details/<?= (int) $spot['id'] ?>">
                            <?= htmlspecialchars($spot['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h3>
                    <p>
                        <?= htmlspecialchars($spot['category'], ENT_QUOTES, 'UTF-8') ?> •
                        <?= htmlspecialchars($spot['location'], ENT_QUOTES, 'UTF-8') ?> •
                        GPS: <?= htmlspecialchars($spot['coordinates'], ENT_QUOTES, 'UTF-8') ?> •
                        ★ <?= htmlspecialchars((string) $spot['rating'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <footer>
        <p>By continuing, you agree to SpotScout Community Guidelines &amp; Terms.</p>
    </footer>
</body>
</html>