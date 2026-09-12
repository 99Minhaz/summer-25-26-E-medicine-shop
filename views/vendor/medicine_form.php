<?php require BASE_PATH . '/views/vendor/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1><?= $isEdit ? 'Edit Medicine' : 'Add Medicine' ?></h1>
        <p class="muted">JPEG or PNG medicine image, maximum 2MB. Listed under your vendor name automatically.</p>
    </div>
</section>

<section class="panel">
    <form method="post" class="grid-form" enctype="multipart/form-data" data-validate="medicine" novalidate>
        <?= csrf_field() ?>
        <label>
            <span>Name</span>
            <input type="text" name="name" value="<?= e($medicine['name'] ?? '') ?>" required maxlength="150">
            <?php $name = 'name'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>Category</span>
            <select name="category_id" required>
                <option value="">Choose category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['id']) ?>" <?= ((string) ($medicine['category_id'] ?? '') === (string) $category['id']) ? 'selected' : '' ?>>
                        <?= e($category['name']) ?> (<?= e($category['category_type']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php $name = 'category_id'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>Price</span>
            <input type="number" name="price" value="<?= e($medicine['price'] ?? '') ?>" min="0.01" step="0.01" required>
            <?php $name = 'price'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>Availability</span>
            <input type="number" name="availability" value="<?= e($medicine['availability'] ?? '') ?>" min="0" step="1" required>
            <?php $name = 'availability'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <label>
            <span>Image</span>
            <input type="file" name="image" accept="image/jpeg,image/png">
            <?php $name = 'image'; require BASE_PATH . '/views/layout/_error.php'; ?>
            <?php if (!empty($medicine['image_path'])): ?>
                <small class="muted">Current: <?= e($medicine['image_path']) ?></small>
            <?php endif; ?>
        </label>

        <label class="full">
            <span>Description</span>
            <textarea name="description" rows="4" maxlength="1000"><?= e($medicine['description'] ?? '') ?></textarea>
            <?php $name = 'description'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>

        <div class="form-actions full">
            <a class="button" href="<?= url('/vendor/medicines') ?>">Cancel</a>
            <button class="button primary" type="submit"><?= $isEdit ? 'Save Changes' : 'Create Medicine' ?></button>
        </div>
    </form>
</section>
