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
                <li><a href="/register">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section aria-labelledby="login-heading">
            <h1 id="login-heading">Welcome Back</h1>
            <p>Sign in to access your saved spots, location coordinates, and scouting notes.</p>

            <form action="/login" method="post">
                <div>
                    <label for="email">Username or E-mail</label>
                    <input
                        type="text"
                        id="email"
                        name="email"
                        placeholder="e.g. pilot@spotscout.io"
                        required
                    >
                </div>

                <div>
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div>
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#forgot-password">Forgot Password?</a>
                </div>

                <button type="submit">Log In →</button>
            </form>

            <footer>
                <p>Don't have an account? <a href="/register">Sign Up</a></p>
            </footer>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 SpotScout. Precision Location Scouting Tools.</p>
    </footer>
</body>
</html>