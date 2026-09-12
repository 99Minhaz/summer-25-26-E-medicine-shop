<section class="auth-shell">
    <div class="panel narrow">
        <h1>Login</h1>
        <p class="muted">Use your admin or customer account to continue.</p>

        <?php if (!empty($errors['login'])): ?>
            <div class="alert error"><?= e($errors['login']) ?></div>
        <?php endif; ?>

        <form method="post" class="stacked-form" data-validate="login" novalidate>
            <?= csrf_field() ?>
            <label>
                <span>Email</span>
                <input type="email" name="email" value="<?= field_value($old, 'email') ?>" required>
                <?php $name = 'email'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>

            <label>
                <span>Password</span>
                <input type="password" name="password" required>
                <?php $name = 'password'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>

            <label class="check-row">
                <input type="checkbox" name="remember" value="1">
                <span>Remember me</span>
            </label>

            <button class="button primary" type="submit">Login</button>
        </form>
    </div>
</section>
