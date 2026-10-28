    <header class="site-header">
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
        </div>
    </header>
