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

        /* Responsywny layout nagłówka (Mobile-First) */
        html, body {
            max-width: 100vw;
            overflow-x: hidden;
        }
        .header-inner {
            flex-wrap: wrap;
        }
        .header-inner .brand-logo { order: 1; }
        .header-inner .theme-toggle {
            order: 2;
            margin-left: auto;
        }
        .header-inner .search-form {
            box-sizing: border-box;
            order: 3;
            width: 100%;
            margin-top: 1rem;
            min-width: 0;
        }

        @media (min-width: 48rem) {
            .header-inner .search-form {
                order: 2;
                width: auto;
                flex: 1;
                margin-top: 0;
                margin-inline: 2rem;
            }
            .header-inner .desktop-nav { order: 3; }
            .header-inner .theme-toggle {
                order: 4;
                margin-left: 0;
            }
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/spot/add" class="btn btn-primary">+ New Spot</a></li>
                    <li><a href="/profile" class="profile-pill"><?= htmlspecialchars((string) ($_SESSION['user_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></li>
                    <li>
                        <form action="/logout" method="post">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="profile-pill">Log out</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a href="/login" class="profile-pill">Log in</a></li>
                    <li><a href="/register" class="profile-pill">Sign up</a></li>
                <?php endif; ?>
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
<script src="/js/script.js" defer></script>
