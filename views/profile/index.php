<section class="section-head">
    <div>
        <h1>Profile</h1>
        <p class="muted">Update account details, picture, and password.</p>
    </div>
</section>

<section class="panel">
    <form method="post" class="grid-form" enctype="multipart/form-data" data-validate="profile" novalidate>
        <?= csrf_field() ?>

        <label>
            <span>Name</span>
            <input type="text" name="name" value="<?= e($user['name'] ?? '') ?>" required maxlength="100">
            <?php $name = 'name'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>Email</span>
            <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required>
            <?php $name = 'email'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>Phone</span>
            <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" required>
            <?php $name = 'phone'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>Profile Picture</span>
            <input type="file" name="profile_picture" accept="image/jpeg,image/png">
            <?php $name = 'profile_picture'; require BASE_PATH . '/views/layout/_error.php'; ?>
            <?php if (!empty($user['profile_picture'])): ?>
                <small class="muted">Current: <?= e($user['profile_picture']) ?></small>
            <?php endif; ?>
        </label>

        <label class="full">
            <span>Address</span>
            <textarea name="address" rows="3" required><?= e($user['address'] ?? '') ?></textarea>
            <?php $name = 'address'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>Current Password</span>
            <input type="password" name="current_password">
            <?php $name = 'current_password'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>New Password</span>
            <input type="password" name="new_password" minlength="8">
            <?php $name = 'new_password'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <div class="form-actions full">
            <button class="button primary" type="submit">Update Profile</button>
        </div>
    </form>
</section>
