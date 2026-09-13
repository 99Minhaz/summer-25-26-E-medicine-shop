<section class="auth-shell">
    <div class="panel narrow">
        <h1>Create Account</h1>
        <p class="muted">Register as an admin or customer with secure password hashing.</p>

        <form method="post" class="stacked-form" data-validate="register" novalidate>
            <?= csrf_field() ?>
            <label>
                <span>Name</span>
                <input type="text" name="name" value="<?= field_value($old, 'name') ?>" required maxlength="100">
                <?php $name = 'name'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>

            <label>
                <span>Email</span>
                <input type="email" name="email" value="<?= field_value($old, 'email') ?>" required>
                <?php $name = 'email'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>

            <label>
                <span>Password</span>
                <input type="password" name="password" minlength="8" required>
                <?php $name = 'password'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>

            <label>
                <span>Role</span>
                <select name="role" required>
                    <option value="customer" <?= (($old['role'] ?? '') === 'customer') ? 'selected' : '' ?>>Customer</option>
                    <option value="admin" <?= (($old['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
                <?php $name = 'role'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>

            <label>
                <span>Address</span>
                <textarea name="address" rows="3" required><?= field_value($old, 'address') ?></textarea>
                <?php $name = 'address'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>

            <label>
                <span>Phone</span>
                <input type="tel" name="phone" value="<?= field_value($old, 'phone') ?>" required>
                <?php $name = 'phone'; require BASE_PATH . '/views/layout/_error.php'; ?>
            </label>

            <button class="button primary" type="submit">Register</button>
        </form>
    </div>
</section>
