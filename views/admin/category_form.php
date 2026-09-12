<?php require BASE_PATH . '/views/admin/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>Edit Category</h1>
        <p class="muted">Update category name and liquid/solid segmentation.</p>
    </div>
</section>

<section class="panel narrow">
    <form method="post" class="stacked-form" data-validate="category" novalidate>
        <?= csrf_field() ?>
        <label>
            <span>Name</span>
            <input type="text" name="name" value="<?= e($category['name'] ?? '') ?>" required maxlength="120">
            <?php $name = 'name'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>
        <label>
            <span>Type</span>
            <select name="category_type" required>
                <option value="solid" <?= (($category['category_type'] ?? '') === 'solid') ? 'selected' : '' ?>>Solid</option>
                <option value="liquid" <?= (($category['category_type'] ?? '') === 'liquid') ? 'selected' : '' ?>>Liquid</option>
            </select>
            <?php $name = 'category_type'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>
        <div class="form-actions">
            <a class="button" href="<?= url('/admin/categories') ?>">Cancel</a>
            <button class="button primary" type="submit">Save Changes</button>
        </div>
    </form>
</section>
