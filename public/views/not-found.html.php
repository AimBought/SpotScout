<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body>
    <main>
        <h1>404 – Location Not Found</h1>
        <p><?= htmlspecialchars($errorMessage ?? 'Nie znaleziono strony o podanym adresie.', ENT_QUOTES, 'UTF-8') ?></p>
        <nav aria-label="Error recovery">
            <a href="/">Return to Welcome Screen</a> |
            <a href="/explore">Go to Explore Feed</a>
        </nav>
    </main>
</body>
</html>