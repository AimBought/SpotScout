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
        <nav aria-label="Auth navigation">
            <ul>
                <li><a href="/explore">Explore Feed</a></li>
                <li><a href="/login">Log In</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section aria-labelledby="register-heading">
            <h1 id="register-heading">Join SpotScout</h1>
            <p>Join thousands of filmmakers, photographers, and explorers scouting unique locations worldwide.</p>

            <form action="/register" method="post">
                <div>
                    <label for="username">Username (Unique Handle)</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="@lens_explorer"
                        required
                    >
                </div>

                <div>
                    <label for="email">E-mail Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="alex@cineframe.io"
                        required
                    >
                </div>

                <div>
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="At least 8 characters"
                        minlength="8"
                        required
                    >
                </div>

                <div>
                    <label for="password-repeat">Repeat Password</label>
                    <input
                        type="password"
                        id="password-repeat"
                        name="password_repeat"
                        placeholder="Re-enter password"
                        minlength="8"
                        required
                    >
                </div>

                <div>
                    <label>
                        <input type="checkbox" name="terms" required>
                        I agree to the Community Scouting Guidelines &amp; Terms
                    </label>
                </div>

                <button type="submit">Sign Up →</button>
            </form>

            <footer>
                <p>Already have an account? <a href="/login">Log In</a></p>
            </footer>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 SpotScout. Precision Location Scouting Tools.</p>
    </footer>
</body>
</html>