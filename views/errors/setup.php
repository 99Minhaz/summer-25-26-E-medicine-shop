<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= asset('assets/css/styles.css') ?>">
</head>
<body>
    <main class="page">
        <section class="panel narrow">
            <p class="eyebrow">Setup check</p>
            <h1><?= e($title) ?></h1>
            <p class="muted">The PHP app is loading, but it cannot reach the MySQL database yet.</p>

            <div class="alert error"><?= e($message) ?></div>

            <ol class="setup-list">
                <li>Start Apache and MySQL from XAMPP.</li>
                <li>Open <code>http://localhost/Online%20Medicine%20Shop/</code> again.</li>
                <li>If your teacher asks for SQL proof, import <code>schema.sql</code> manually.</li>
            </ol>
        </section>
    </main>
</body>
</html>
