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
            <nav aria-label="Auth navigation">
                <ul class="nav-list">
                    <li><a href="/explore">Explore</a></li>
                    <li><a href="/login" class="btn btn-outline">Log In</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="page auth-wrapper">
        <section class="auth-card" aria-labelledby="register-heading">
            <header class="auth-header">
                <h1 id="register-heading">Join SpotScout</h1>
                <p class="text-muted">Join thousands of filmmakers, photographers, and explorers worldwide.</p>
            </header>

            <form action="/register" method="post" class="form-stack">
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="username">Username</label>
                        <span class="form-hint">Unique Handle</span>
                    </div>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="@lens_explorer"
                        required
                    >
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="email">E-mail</label>
                        <span class="form-hint">Account Email</span>
                    </div>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="alex@cineframe.io"
                        required
                    >
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password">Password</label>
                        <span class="form-hint">Secured</span>
                    </div>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="At least 8 characters"
                        minlength="8"
                        required
                    >
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="password-repeat">Repeat Password</label>
                        <span class="form-hint">Confirm</span>
                    </div>
                    <input
                        type="password"
                        id="password-repeat"
                        name="password_repeat"
                        class="form-input"
                        placeholder="Re-enter password"
                        minlength="8"
                        required
                    >
                </div>

                <label class="checkbox-label">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the Community Scouting Guidelines &amp; Terms</span>
                </label>

                <button type="submit" class="btn btn-primary btn-block">Sign Up →</button>
            </form>

            <footer class="auth-footer">
                <p style="margin: 0;">Already have an account? <a href="/login"><strong>Log In</strong></a></p>
            </footer>
        </section>
    </main>

    <footer class="page site-footer">
        <p>&copy; 2026 SpotScout. Precision Location Scouting Tools.</p>
    </footer>
</body>
</html>