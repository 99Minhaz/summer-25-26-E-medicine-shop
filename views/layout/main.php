<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? APP_NAME) ?> | <?= e(APP_NAME) ?></title>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/styles.css') ?>">
</head>
<body data-base-url="<?= e(base_path()) ?>">
    <header class="site-header">
        <a class="brand" href="<?= url('/') ?>">
            <span class="brand-mark">+</span>
            <span><?= e(APP_NAME) ?></span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Toggle navigation">Menu</button>

        <nav class="nav" data-nav>
            <a href="<?= url('/') ?>">Home</a>

            <?php if (current_role() === 'admin'): ?>
                <a href="<?= url('/admin') ?>">Admin</a>
            <?php endif; ?>

            <?php if (current_role() === 'vendor'): ?>
                <a href="<?= url('/vendor') ?>">Vendor</a>
            <?php endif; ?>

            <?php if (current_role() === 'delivery'): ?>
                <a href="<?= url('/delivery') ?>">Delivery</a>
            <?php endif; ?>

            <?php if (current_role() === 'customer'): ?>
                <a href="<?= url('/cart') ?>">
                    Cart <span class="cart-count" data-cart-count><?= e(cart_count(current_user_id())) ?></span>
                </a>
            <?php endif; ?>

            <?php if (current_user_id()): ?>
                <a href="<?= url('/profile') ?>">Profile</a>
                <form class="nav-form" method="post" action="<?= url('/logout') ?>">
                    <?= csrf_field() ?>
                    <button type="submit">Logout</button>
                </form>
            <?php else: ?>
                <a href="<?= url('/login') ?>">Login</a>
                <a class="nav-cta" href="<?= url('/register') ?>">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="page">
        <?php $flashSuccess = flash('success'); ?>
        <?php if ($flashSuccess): ?>
            <div class="alert success"><?= e($flashSuccess) ?></div>
        <?php endif; ?>

        <?php $flashError = flash('error'); ?>
        <?php if ($flashError): ?>
            <div class="alert error"><?= e($flashError) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="footer">
        <p>&copy; <?= e(date('Y')) ?> <?= e(APP_NAME) ?>. All rights reserved.</p>
    </footer>

    <script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
