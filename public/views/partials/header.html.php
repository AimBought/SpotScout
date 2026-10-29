<header class="site-header">
    <style>
        .theme-toggle {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .theme-toggle:focus-visible { outline: none; }
        .theme-toggle-track {
            position: relative;
            width: 56px;
            height: 30px;
            background-color: var(--color-muted, #667085);
            border: 1px solid var(--color-border, #d9e0eb);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 4px;
            transition: background-color 0.3s ease;
        }
        html[data-theme="dark"] .theme-toggle-track {
            background-color: var(--color-brand, #3b82f6);
        }
        .theme-toggle-thumb {
            position: absolute;
            top: 1px;
            left: 2px;
            width: 24px;
            height: 24px;
            background-color: #ffffff;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
            z-index: 2;
        }
        html[data-theme="dark"] .theme-toggle-thumb {
            transform: translateX(26px);
        }
        .theme-toggle-icon-sun,
        .theme-toggle-icon-moon {
            font-size: 13px;
            z-index: 1;
            user-select: none;
        }
    </style>

    <div class="page header-inner">
        <a href="/" class="brand-logo">
            SpotScout <span class="brand-dot" aria-hidden="true"></span>
        </a>

        <form action="/explore" method="get" role="search" class="search-form">
            <label for="search-spots" class="sr-only">Search spots</label>
            <input
                type="search"
                id="search-spots"
                name="q"
                class="search-input"
                placeholder="Search spots, coordinates, tags..."
            >
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <nav class="desktop-nav" aria-label="Main navigation">
            <ul class="nav-list">
                <li><a href="/">Welcome</a></li>
                <li><a href="/explore" aria-current="page" class="active-link">Explore</a></li>
                <li><a href="/spot/add" class="btn btn-primary">+ New Spot</a></li>
                <li><a href="/profile" class="profile-pill">@adam_k</a></li>
            </ul>
        </nav>

        <button class="theme-toggle" type="button" aria-label="Włącz ciemny motyw" aria-pressed="false" title="Zmień motyw">
            <span class="theme-toggle-track">
                <span class="theme-toggle-thumb"></span>
                <span class="theme-toggle-icon-sun" aria-hidden="true">☀️</span>
                <span class="theme-toggle-icon-moon" aria-hidden="true">🌙</span>
            </span>
        </button>
    </div>
</header>