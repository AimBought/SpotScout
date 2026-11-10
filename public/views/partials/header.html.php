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
        .profile-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            flex: 0 0 32px;
        }
        .logout-form { margin: 0; }
        .logout-link {
            background: none;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-pill);
            padding: 0.45rem 0.85rem;
            cursor: pointer;
            color: var(--color-text);
            font: inherit;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .logout-link:hover { background: var(--color-surface-alt); border-color: var(--color-brand); }
        .desktop-nav .nav-list { flex-wrap: nowrap; gap: 0.25rem; }
        .desktop-nav .nav-list > li { display: flex; align-items: center; }
        .desktop-nav .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: 0.55rem 0.85rem;
            border: 1px solid transparent;
            border-radius: var(--radius-pill);
            color: var(--color-muted);
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            transition: color 0.15s ease, background-color 0.15s ease, border-color 0.15s ease;
        }
        .desktop-nav .nav-link:hover,
        .desktop-nav .nav-link[aria-current="page"] {
            color: var(--color-brand);
            background: color-mix(in srgb, var(--color-brand) 10%, var(--color-surface));
            border-color: color-mix(in srgb, var(--color-brand) 22%, var(--color-border));
        }
        .profile-pill {
            display: inline-flex;
            align-items: center;
            min-height: 2.75rem;
            padding: 0.3rem 0.75rem;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-pill);
            background: var(--color-surface-alt);
            color: var(--color-text);
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }
        .profile-pill:hover { border-color: var(--color-brand); color: var(--color-brand); }
        .logout-link { min-height: 2.75rem; white-space: nowrap; }
        .role-badge {
            display: inline-flex;
            align-items: center;
            border: 1px solid color-mix(in srgb, var(--color-brand) 35%, var(--color-border));
            border-radius: var(--radius-pill);
            padding: .2rem .55rem;
            color: var(--color-brand);
            font-size: .75rem;
            font-weight: 700;
            line-height: 1.2;
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
        <a href="<?= isset($_SESSION['user_id']) ? '/explore' : '/' ?>" class="brand-logo">
            SpotScout <span class="brand-dot" aria-hidden="true"></span>
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
        <form action="/explore" method="get" role="search" class="search-form" id="spot-search-form">
            <label for="search-spots" class="sr-only">Search spots</label>
            <input
                type="search"
                id="search-spots"
                name="q"
                class="search-input"
                value="<?= htmlspecialchars(is_string($_GET['q'] ?? null) ? $_GET['q'] : '', ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search spots, coordinates, tags..."
            >
            <?php $spotSearchCategories = ['Architecture', 'Industrial', 'Urban', 'Nature', 'Drone Friendly', 'Night']; ?>
            <?php $selectedSearchCategories = isset($_GET['categories']) && is_array($_GET['categories']) ? array_filter($_GET['categories'], 'is_string') : []; ?>
            <details class="search-filter">
                <summary class="search-filter-button" aria-label="Filter by categories" title="Filter by categories">
                    <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16l-6.5 7.5V19l-3 1v-7.5L4 5z"/></svg>
                    <span class="sr-only">Filter by categories</span>
                </summary>
                <div class="search-filter-menu">
                    <strong>Filter categories</strong>
                    <?php foreach ($spotSearchCategories as $searchCategory): ?>
                        <label><input type="checkbox" name="categories[]" value="<?= htmlspecialchars($searchCategory, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($searchCategory, $selectedSearchCategories, true) ? 'checked' : '' ?>><span><?= htmlspecialchars($searchCategory, ENT_QUOTES, 'UTF-8') ?></span></label>
                    <?php endforeach; ?>
                </div>
            </details>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        <?php endif; ?>

        <nav class="desktop-nav" aria-label="Main navigation">
            <ul class="nav-list">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/spot/add" class="btn btn-primary">+ New Spot</a></li>
                    <li><a href="/profile" class="profile-pill profile-link"><img class="header-avatar" src="<?= htmlspecialchars((string) ($_SESSION['user_avatar_path'] ?? '/images/default-avatar.svg'), ENT_QUOTES, 'UTF-8') ?>" alt="" width="32" height="32"><span><?= htmlspecialchars((string) ($_SESSION['user_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span><?php if (in_array($_SESSION['user_role'] ?? null, ['moderator', 'admin'], true)): ?><span class="role-badge"><?= htmlspecialchars((string) $_SESSION['user_role'] === 'admin' ? 'Admin' : 'Moderator', ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></a></li>
                    <li>
                        <form action="/logout" method="post" class="logout-form">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="logout-link">Log out</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li><a href="/" class="nav-link">Welcome</a></li>
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
<script src="/js/script.js?v=spot-search-2" defer></script>
