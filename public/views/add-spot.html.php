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
                <li><a href="/spot/add" aria-current="page">+ Add Spot</a></li>
                <li><a href="/profile">My Profile</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section aria-labelledby="add-spot-heading">
            <header>
                <h1 id="add-spot-heading">Add New Spot</h1>
                <p>Contribute your scouted location to the creative community.</p>
            </header>

            <form action="/spot/add" method="post" enctype="multipart/form-data">
                <fieldset>
                    <legend>Location Imagery</legend>
                    <label for="photos">Add Photos (JPEG, PNG, HEIC)</label>
                    <input type="file" id="photos" name="photos[]" multiple accept="image/*">
                    <p><small>Upload photos documenting the actual state of the location.</small></p>
                </fieldset>

                <fieldset>
                    <legend>Basic Information</legend>
                    <div>
                        <label for="spot-name">Spot Name *</label>
                        <input
                            type="text"
                            id="spot-name"
                            name="name"
                            placeholder="e.g. Old Power Plant Turbine Hall"
                            required
                        >
                    </div>

                    <div>
                        <p>Spot Categories:</p>
                        <?php foreach ($categories as $category): ?>
                            <label>
                                <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Coordinates &amp; Geolocation</legend>
                    <div>
                        <label for="latitude">Latitude</label>
                        <input type="text" id="latitude" name="latitude" placeholder="e.g. 50.8430° N" required>
                    </div>
                    <div>
                        <label for="longitude">Longitude</label>
                        <input type="text" id="longitude" name="longitude" placeholder="e.g. 16.4880° E" required>
                    </div>
                    <button type="button">Get Current GPS Location</button>
                </fieldset>

                <fieldset>
                    <legend>Description &amp; Scouting Notes</legend>
                    <div>
                        <label for="description">Your Note / Description</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            placeholder="Describe parking, access, best lighting hours, sunrise/sunset vantage points..."
                            required
                        ></textarea>
                    </div>

                    <div>
                        <label for="rating">Initial Suitability Rating (1-5)</label>
                        <input type="number" id="rating" name="rating" min="1" max="5" step="0.5" value="4.5">
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Practical Parameters &amp; Rules</legend>
                    <div>
                        <label>
                            <input type="checkbox" name="drone_permitted" value="1">
                            <strong>Drone Permitted</strong> – Safe airspace for drone flights
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" name="tripod_friendly" value="1">
                            <strong>Tripod Friendly</strong> – Stable ground, no restrictions
                        </label>
                    </div>
                    <div>
                        <label>
                            <input type="checkbox" name="requires_permit" value="1">
                            <strong>Requires Permit</strong> – Commercial filming permit or manager approval needed
                        </label>
                    </div>
                </fieldset>

                <div>
                    <button type="button">Save Draft</button>
                    <button type="submit">Submit Location</button>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 SpotScout. Locations are verified against community safety guidelines.</p>
    </footer>
</body>
</html>