<?php require BASE_PATH . '/views/admin/_nav.php'; ?>

<section class="section-head">
    <div>
        <h1>Category Management</h1>
        <p class="muted">Create, edit, and delete medicine genres.</p>
    </div>
</section>

<section class="admin-split">
    <form method="post" class="panel stacked-form" data-validate="category" novalidate>
        <?= csrf_field() ?>
        <h2>Add Category</h2>
        <label>
            <span>Name</span>
            <input type="text" name="name" value="<?= field_value($old, 'name') ?>" required maxlength="120">
            <?php $name = 'name'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>
        <label>
            <span>Type</span>
            <select name="category_type" required>
                <option value="">Choose type</option>
                <option value="solid" <?= (($old['category_type'] ?? '') === 'solid') ? 'selected' : '' ?>>Solid</option>
                <option value="liquid" <?= (($old['category_type'] ?? '') === 'liquid') ? 'selected' : '' ?>>Liquid</option>
            </select>
            <?php $name = 'category_type'; require BASE_PATH . '/views/layout/_error.php'; ?>
        </label>
        <button class="button primary" type="submit">Create Category</button>
    </form>

    <div class="panel">
        <h2>Existing Categories</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Medicines</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= e($category['name']) ?></td>
                            <td><?= e($category['category_type']) ?></td>
                            <td><?= e($category['medicine_count']) ?></td>
                            <td class="actions">
                                <a class="button small" href="<?= url('/admin/categories/edit/' . $category['id']) ?>">Edit</a>
                                <form method="post" action="<?= url('/admin/categories/delete/' . $category['id']) ?>" data-confirm="Delete this category?">
                                    <?= csrf_field() ?>
                                    <button class="button small danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
