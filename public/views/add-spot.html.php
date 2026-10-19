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
                    <li><a href="/profile">@adam_k</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="page form-container">
        <header style="margin-bottom: var(--space-3);">
            <span class="radar-status">● NEW SUBMISSION</span>
            <h1 id="add-spot-heading">Add New Spot</h1>
            <p class="text-muted">Contribute your scouted location and practical notes to the creative community.</p>
        </header>

        <form action="/spot/add" method="post" enctype="multipart/form-data">
            <section class="content-card" aria-labelledby="imagery-heading">
                <div class="form-label-row" style="margin-bottom: var(--space-1);">
                    <h2 id="imagery-heading" style="margin: 0;">Location Imagery</h2>
                    <span class="form-hint">JPEG, PNG, HEIC</span>
                </div>
                <div class="upload-dropzone">
                    <p style="margin-bottom: 0.5rem;"><strong>Tap to upload photos</strong> or drag frames directly</p>
                    <p class="text-muted" style="font-size: 0.85rem;">Show the actual state of the location and lighting conditions</p>
                    <input type="file" id="photos" name="photos[]" multiple accept="image/*">
                </div>
            </section>

            <section class="content-card form-stack" aria-labelledby="basic-info-heading">
                <h2 id="basic-info-heading" style="margin: 0;">Spot Details &amp; Categories</h2>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="spot-name">Spot Name</label>
                        <span class="form-hint">Required</span>
                    </div>
                    <input
                        type="text"
                        id="spot-name"
                        name="name"
                        class="form-input"
                        placeholder="e.g. Old Power Plant Turbine Hall"
                        required
                    >
                </div>

                <div class="form-group">
                    <span class="form-label-row">Spot Categories</span>
                    <div class="check-pills-grid">
                        <?php foreach ($categories as $category): ?>
                            <label class="check-pill">
                                <input type="checkbox" name="categories[]" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>">
                                <span><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section class="content-card form-stack" aria-labelledby="geo-heading">
                <div class="form-row-between">
                    <h2 id="geo-heading" style="margin: 0;">Coordinates &amp; Geolocation</h2>
                    <span class="badge">GPS Ready</span>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="latitude" class="form-label-row">Latitude</label>
                        <input type="text" id="latitude" name="latitude" class="form-input" placeholder="50.8430° N" required>
                    </div>
                    <div class="form-group">
                        <label for="longitude" class="form-label-row">Longitude</label>
                        <input type="text" id="longitude" name="longitude" class="form-input" placeholder="16.4880° E" required>
                    </div>
                </div>

                <button type="button" class="btn btn-outline">📍 Get Current Location</button>
            </section>

            <section class="content-card form-stack" aria-labelledby="desc-heading">
                <h2 id="desc-heading" style="margin: 0;">Description &amp; Suitability</h2>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="description">Your Note / Description</label>
                        <span class="form-hint">Markdown supported</span>
                    </div>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="form-textarea"
                        placeholder="Describe parking, access, best lighting hours, sunrise/sunset vantage points..."
                        required
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="rating" class="form-label-row">Rate This Spot (1-5)</label>
                    <input type="number" id="rating" name="rating" class="form-input" min="1" max="5" step="0.5" value="4.5">
                </div>
            </section>

            <section class="content-card form-stack" aria-labelledby="logistics-heading">
                <h2 id="logistics-heading" style="margin: 0;">Field Logistics &amp; Regulations</h2>

                <label class="rule-option-card">
                    <input type="checkbox" name="drone_permitted" value="1" checked>
                    <div>
                        <strong>Drone Permitted</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Safe open area for drone flights</p>
                    </div>
                </label>

                <label class="rule-option-card">
                    <input type="checkbox" name="tripod_friendly" value="1" checked>
                    <div>
                        <strong>Tripod Friendly</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Firm ground, no tripod restrictions</p>
                    </div>
                </label>

                <label class="rule-option-card">
                    <input type="checkbox" name="requires_permit" value="1">
                    <div>
                        <strong>Requires Permit</strong>
                        <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Commercial filming permit or property manager approval needed</p>
                    </div>
                </label>
            </section>

            <div class="hero-actions" style="justify-content: flex-end;">
                <button type="button" class="btn btn-outline">Save Draft</button>
                <button type="submit" class="btn btn-primary">Submit Location →</button>
            </div>
        </form>
    </main>

    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <a href="/explore" class="mobile-nav-item">Explore</a>
        <a href="/spot/add" class="mobile-nav-item mobile-nav-add is-active">+ Add</a>
        <a href="/profile" class="mobile-nav-item">Profile</a>
    </nav>

    <footer class="page site-footer">
        <p>&copy; 2026 SpotScout. Location will be verified against community safety guidelines.</p>
    </footer>
</body>
</html>