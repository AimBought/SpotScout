<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/partials/header.html.php'; ?>

    <main class="page auth-wrapper">
        <section class="auth-card" aria-labelledby="login-heading">
            <header class="auth-header">
                <h1 id="login-heading">Welcome Back</h1>
                <p class="text-muted">Sign in to access your saved spots, coordinates, and scouting notes.</p>
            </header>

            <form action="/login" method="post" class="form-stack">
                <div class="form-group">
                    <div class="form-label-row">
                        <label for="email">Username or E-mail</label>
                        <span class="form-hint">Scout ID</span>
                    </div>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="e.g. pilot@spotscout.io"
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
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="form-row-between">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Log In →</button>
            </form>

            <footer class="auth-footer">
                <p style="margin: 0;">Don't have an account? <a href="/register"><strong>Sign Up</strong></a></p>
            </footer>
        </section>
    </main>

    <footer class="page site-footer">
        <p>&copy; 2026 SpotScout. Precision Location Scouting Tools.</p>
    </footer>
</body>
</html>
