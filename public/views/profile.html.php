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
        <nav aria-label="Main navigation">
            <ul>
                <li><a href="/explore">Explore Feed</a></li>
                <li><a href="/spot/add">+ Add Spot</a></li>
                <li><a href="/profile" aria-current="page">My Profile</a></li>
                <li><a href="/login">Log Out</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section aria-labelledby="profile-name">
            <header>
                <h1 id="profile-name"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p>
                    <strong><?= htmlspecialchars($user['handle'], ENT_QUOTES, 'UTF-8') ?></strong> •
                    <span><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span> •
                    <span><?= htmlspecialchars($user['location'], ENT_QUOTES, 'UTF-8') ?></span>
                </p>
                <p><?= htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8') ?></p>

                <div>
                    <button type="button">Edit Profile</button>
                    <button type="button">Share Portfolio</button>
                </div>
            </header>

            <section aria-label="User statistics">
                <h2>Scout Statistics</h2>
                <dl>
                    <dt>Scout Points</dt>
                    <dd><?= (int) $user['points'] ?></dd>

                    <dt>Locations Added</dt>
                    <dd><?= (int) $user['addedCount'] ?></dd>

                    <dt>Total Saves</dt>
                    <dd><?= htmlspecialchars($user['savesCount'], ENT_QUOTES, 'UTF-8') ?></dd>

                    <dt>Community Rank</dt>
                    <dd><?= htmlspecialchars($user['rank'], ENT_QUOTES, 'UTF-8') ?></dd>
                </dl>
            </section>

            <section aria-label="Camera and drone gear">
                <h2>My Gear &amp; Equipment</h2>
                <ul>
                    <?php foreach ($user['gear'] as $item): ?>
                        <li><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </section>

        <hr>

        <section aria-labelledby="my-spots-heading">
            <header>
                <h2 id="my-spots-heading">My Added Spots (<?= count($mySpots) ?>)</h2>
                <nav aria-label="Spot status tabs">
                    <a href="/profile?tab=published">Published</a> |
                    <a href="/profile?tab=drafts">Drafts</a> |
                    <a href="/profile?tab=saved">Saved &amp; Bookmarked</a>
                </nav>
            </header>

            <?php foreach ($mySpots as $spot): ?>
                <article>
                    <h3>
                        <a href="/spot/details/<?= (int) $spot['id'] ?>">
                            <?= htmlspecialchars($spot['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </h3>
                    <p>
                        <?= htmlspecialchars($spot['location'], ENT_QUOTES, 'UTF-8') ?> •
                        GPS: <?= htmlspecialchars($spot['coordinates'], ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p>
                        Rating: ★ <?= htmlspecialchars((string) $spot['rating'], ENT_QUOTES, 'UTF-8') ?> •
                        ♥ <?= (int) $spot['saves'] ?> saves
                    </p>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 SpotScout.</p>
    </footer>
</body>
</html>